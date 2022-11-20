/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * SiegeOfCranes implementation : © Brock Turner <brocam@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * siegeofcranes.js
 *
 * SiegeOfCranes user interface script
 *
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

define([
    "dojo",
    "dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter",
    "ebg/stock"
],
function (dojo, declare) {
    return declare("bgagame.siegeofcranes", ebg.core.gamegui, {
        constructor: function(){
            console.log('siegeofcranes constructor');
            this.cardWidth = 100;
            this.cardHeight = 143;
            this.iconWidth = 64;
            this.iconHeight = 64;
            this.backupDescriptionMyTurn = '';
            this.isCoyoteState = true;

            // Here, you can init the global variables of your user interface
            // Example:
            // this.myGlobalValue = 0;

        },

        /*
            setup:

            This method must set up the game user interface according to current game situation specified
            in parameters.

            The method is called each time the game interface is displayed to a player, ie:
            _ when the game starts
            _ when a player refreshes the game page (F5)

            "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
        */

        setup: function(gamedatas)
        {
            console.log( "Starting game setup" );

            // set up player hand
            this.playerHand = this.setupStock('myhand', 'cards.jpg', this.cardWidth, this.cardHeight);
            this.playerHand.setSelectionMode(0);
            this.playerHand.extraClasses='card';
            this.playerHand.onItemCreate = dojo.hitch(this, 'setupHandCard');

            // Cards in player's hand
            for (var i in this.gamedatas.hand) {
                var card = this.gamedatas.hand[i];
                this.playerHand.addToStockWithId(card.type, card.id);
            }

            // Setting up all players' collections
            this.playersCollection = [];
            this.playersHandCount = [];
            for (var playerId in gamedatas.players) {
                this.playersCollection[playerId] = this.setupStock(`playercollection_${playerId}`, 'icons.gif', this.iconWidth, this.iconHeight);
                this.playersCollection[playerId].setSelectionMode(0);
                this.playersCollection[playerId].extraClasses='token';
                this.playersHandCount[playerId] = new ebg.counter();
                this.playersHandCount[playerId].create('handcount_' + playerId);
                this.playersHandCount[playerId].setValue(gamedatas.players[playerId].handcount);
            }

            // Cards played on table
            for (i in this.gamedatas.collections) {
                var card = this.gamedatas.collections[i];
                var playerId = card.location_arg;
                this.addCardToCollection(playerId, card.type, card.id);
            }

            this.secondDeck = gamedatas.seconddeck;

            // setup deck counter
            this.deckCount = new ebg.counter();
            this.deckCount.create('deckcount');
            this.deckCount.setValue(gamedatas.deckcount);

            // setup discard counter
            this.discardCount = new ebg.counter();
            this.discardCount.create('discardcount');
            this.discardCount.setValue(gamedatas.discardcount);

            if (this.gamedatas.topdiscardcard) {
                var type = this.gamedatas.topdiscardcard.type;
                dojo.addClass('discard', 'cardontable');
                dojo.addClass('discard', 'card');
                dojo.style('discard', 'background-position', `-${this.cardTypeX(type)}px -${this.cardTypeY(type)}px`);
            }

            dojo.connect(this.playerHand, 'onChangeSelection', this, 'onPlayerHandSelectionChanged');

            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();

            console.log( "Ending game setup" );
        },


        ///////////////////////////////////////////////////
        //// Game & client states

        // onEnteringState: this method is called each time we are entering into a new game state.
        //                  You can use this method to perform some user interface changes at this moment.
        //
        onEnteringState: function( stateName, args )
        {
            console.log( 'Entering state: '+stateName );

            switch(stateName) {

                case 'selectMultipleCardsToCollect':
                case 'kangarooDiscard':
                case 'giveCards':
                    this.playerHand.setSelectionMode(2);
                    break;

                case 'playerTurn':
                    this.playerHand.setSelectionMode(this.isCurrentPlayerActive() ? 1 : 0);
                    break;

                case 'selectCardToCollect':
                    this.playerHand.setSelectionMode(1);
                    break;

                case 'waitForUndoFoxes':
                case 'waitForRedoFoxes':
                case 'waitForCranes':
                    this.playerHand.setSelectionMode(0);
                    break;
            }
        },

        // onLeavingState: this method is called each time we are leaving a game state.
        //                 You can use this method to perform some user interface changes at this moment.
        //
        onLeavingState: function( stateName )
        {
            console.log( 'Leaving state: '+stateName );

            switch( stateName )
            {

            /* Example:

            case 'myGameState':

                // Hide the HTML block we are displaying only during this game state
                dojo.style( 'my_html_block_id', 'display', 'none' );

                break;
           */


            case 'dummmy':
                break;
            }
        },

        // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
        //                        action status bar (ie: the HTML links in the status bar).
        //
        onUpdateActionButtons: function( stateName, args )
        {
            console.log( 'onUpdateActionButtons: '+stateName );

            if( this.isCurrentPlayerActive() )
            {
                switch( stateName )
                {
                    case 'playerTurn':
                        this.addActionButton('playAction_button', _('Play Action'), 'playAction');
                        this.addActionButton('addToCollection_button', _('Add to Collection'), 'addToCollection');
                        this.addActionButton('drawCards_button', _('Draw Cards'), 'drawCards');

                        dojo.addClass('playAction_button', 'disabled');
                        dojo.addClass('addToCollection_button', 'disabled');
                        break;

                    case 'waitForUndoFoxes':
                        if (this.playerHand.getAllItems().find(card => card.type == 4)) {
                            this.addActionButton('playFox_button', _('Cancel Action'), 'playFox');
                        }
                        this.addActionButton('passFox_button', _('Pass'), 'passFox');
                        break;

                    case 'waitForRedoFoxes':
                        if (this.playerHand.getAllItems().find(card => card.type == 4)) {
                            this.addActionButton('playFox_button', _('Perform Action'), 'playFox');
                        }
                        this.addActionButton('passFox_button', _('Pass'), 'passFox');
                        break;

                    case 'selectMultipleCardsToCollect':
                        this.isCoyoteState = true;
                    case 'selectCardToCollect':
                        this.addActionButton('addToCollection_button', _('Add to Collection'), 'addToCollection');
                        break;

                    case 'kangarooDiscard':
                        this.addActionButton('discardCards_button', _('Discard Cards'), 'discardCards');
                        break;

                    case 'giveCards':
                        this.addActionButton('giveCards_button', _('Give Cards'), 'giveCards');
                        break;

                    case 'waitForCranes':
                        if (this.playerHand.getAllItems().find(card => card.type == 8)) {
                            this.addActionButton('playCrane_button', _('Discard collected cards'), 'playCrane');
                        }
                        this.addActionButton('passCrane_button', _('Pass'), 'passCrane');
                        break;
                }
            }
        },

        ///////////////////////////////////////////////////
        //// Utility methods

        /*

            Here, you can defines some utility methods that you can use everywhere in your javascript
            script.

        */

        cardTypeX: function(type) {
            return this.cardWidth * ((type - 1) % 5);
        },

        cardTypeY: function(type) {
            return this.cardHeight * Math.floor((type - 1) / 5);
        },

        setupStock: function(templateName, imageName, width, height) {
            var stock = new ebg.stock(); // new stock object for hand
            stock.create(this, $(templateName), width, height);

            stock.image_items_per_row = 5;

            for (var type = 1; type <= 10; type++) {
                stock.addItemType(type, type, g_gamethemeurl + 'img/' + imageName, type - 1);
            }

            return stock;
        },

        setupHandCard: function(card_div, card_type_id, card_id) {
            this.addTooltipHtml(card_div.id, this.format_block('jstpl_bigcard', {
                card_id: card_id,
                card_title: this.gamedatas.cardTypes[card_type_id]['name'],
                card_description: this.gamedatas.cardTypes[card_type_id]['description']
            }));
            card_div.onclick = () => {
                console.log("tooltip click", this.tooltips[card_id]);
                // this.tooltips[card_id].open(card_id);
                this.tooltips[card_id].close(card_id);
            };
        },

        moveCardAnimation: function(source, destination, cardType, cardId) {
            return this.slideTemporaryObject(
                this.format_block(
                    'jstpl_cardontable',
                    {
                        x: this.cardTypeX(cardType),
                        y: this.cardTypeY(cardType),
                        card_id: cardId + 't'
                    }
                ),
                destination,
                source,
                destination
            );
        },

        addCardToCollection: function(playerId, cardType, cardId) {
            var source = 'overall_player_board_' + playerId;
            if ($('myhand_item_' + cardId)) {
                source = 'myhand_item_' + cardId;
                this.playerHand.removeFromStockById(cardId);
            }
            this.moveCardAnimation(source, 'playercollection_' + playerId, cardType, cardId).play();

            this.playersCollection[playerId].addToStockWithId(cardType, cardId);
        },

        giveCard: function(playerId, cardType, cardId, destination) {
            var source = 'overall_player_board_' + playerId;
            if ($('myhand_item_' + cardId)) {
                source = 'myhand_item_' + cardId;
                this.playerHand.removeFromStockById(cardId);
            }
            this.moveCardAnimation(source, destination, cardType, cardId).play();
        },

        discardCard: function(playerId, cardType, cardId, topDiscardType) {
            var source = 'overall_player_board_' + playerId;
            if ($('myhand_item_' + cardId)) {
                source = 'myhand_item_' + cardId;
                this.playerHand.removeFromStockById(cardId);
            }
            var animation = this.moveCardAnimation(source, 'discard', cardType, cardId);
            dojo.connect(animation, 'onEnd', dojo.hitch(this, 'updateTopDiscardCard', topDiscardType || cardType));
            animation.play();
        },

        discardCollectedCard: function(playerId, cardType, cardId) {
            var source = 'playercollection_' + playerId;
            this.playersCollection[playerId].removeFromStockById(cardId);
            var animation = this.moveCardAnimation(source, 'discard', cardType, cardId);
            dojo.connect(animation, 'onEnd', dojo.hitch(this, 'updateTopDiscardCard', cardType));
            animation.play();
        },

        addCardsToHand: function(cards, source='deck') {
            console.log('addCardsToHand', source);
            this.slideTemporaryObject(
                this.format_block('jstpl_cardback', {id: 1}),
                'myhand',
                source,
                'myhand'
            ).play();
            for (var cardIndex in cards) {
                var card = cards[cardIndex];
                this.playerHand.addToStockWithId(card.type, card.id);
            }
        },

        ajaxAction: function(action, data) {
            if (!this.checkAction(action)) {
                return;
            }
            this.ajaxcall(
                "/" + this.game_name + "/" +this.game_name + "/" + action + ".html",
                data,
                this,
                function(result) {
                    this.playerHand.unselectAll();
                    for (playerId in this.playersCollection) {
                        this.playersCollection[playerId].unselectAll();
                    }
                },
                function(isError) {}
            );
        },

        updateTopDiscardCard: function(cardType) {
            dojo.addClass('discard', 'cardontable');
            dojo.addClass('discard', 'card');
            dojo.style('discard', 'background-position', `-${this.cardTypeX(cardType)}px -${this.cardTypeY(cardType)}px`);
        },


        ///////////////////////////////////////////////////
        //// Player's action

        /*

            Here, you are defining methods to handle player's action (ex: results of mouse click on
            game objects).

            Most of the time, these methods:
            _ check the action is possible at this game state.
            _ make a call to the game server

        */

        onPlayerHandSelectionChanged: function() {
            var items = this.playerHand.getSelectedItems();
            if ($('playAction_button')) {
                if (items.length > 0) {
                    dojo.removeClass('playAction_button', 'disabled');
                    dojo.removeClass('addToCollection_button', 'disabled');
                } else {
                    dojo.addClass('playAction_button', 'disabled');
                    dojo.addClass('addToCollection_button', 'disabled');
                }
            }
        },

        playFerret: function(cardId, direction) {
            this.ajaxAction('playFerret', {
                id: cardId,
                direction: direction,
                lock: true
            });
        },

        playFinch: function(cardId, giverId) {
            this.ajaxAction('playFinch', {
                id: cardId,
                giver_id: giverId,
                lock: true
            });
        },

        playRat: function(cardId) {
            var targets = [];
            for (playerId in this.playersCollection) {
                targets = targets.concat(this.playersCollection[playerId].getSelectedItems());
            }
            if (targets.length !== 2) {
                this.showMessage('Incorrect number of collected cards selected', 'error');
                return;
            }

            this.ajaxAction('playRat', {
                id: cardId,
                target1_id: targets[0].id,
                target2_id: targets[1].id,
                lock: true
            });

            for (var playerId in this.gamedatas.players) {
                this.playersCollection[playerId].setSelectionMode(0);
            }
        },

        cancelAction: function() {
            this.gamedatas.gamestate.descriptionmyturn = this.backupDescriptionMyTurn;
            this.updatePageTitle();
            this.removeActionButtons();
            this.addActionButton('playAction_button', _('Play Action'), 'playAction');
            this.addActionButton('addToCollection_button', _('Add to Collection'), 'addToCollection');
            this.addActionButton('drawCards_button', _('Draw Cards'), 'drawCards');
        },

        playAction: function() {
            var items = this.playerHand.getSelectedItems();
            if (items.length !== 1) {
                this.showMessage('Select one card to play', 'error');
                return;
            }
            var cardId = items[0].id;

            switch (items[0].type) {
                case "1": // rat card
                    this.backupDescriptionMyTurn = this.gamedatas.gamestate.descriptionmyturn;
                    this.gamedatas.gamestate.descriptionmyturn = _('Choose two collected cards to swap.');
                    this.updatePageTitle();
                    this.removeActionButtons();
                    this.addActionButton('playRat_button', _('Swap cards'), this.playRat.bind(this, cardId));
                    this.addActionButton('cancelAction_button', _('Cancel'), 'cancelAction');

                    for (var playerId in this.gamedatas.players) {
                        this.playersCollection[playerId].setSelectionMode(1);
                    }
                    break;

                case "5": // finch card
                    this.backupDescriptionMyTurn = this.gamedatas.gamestate.descriptionmyturn;
                    this.gamedatas.gamestate.descriptionmyturn = _('Choose a player to give you two cards.');
                    this.updatePageTitle();
                    this.removeActionButtons();
                    for (var playerId in this.gamedatas.players) {
                        //
                        if (playerId != this.player_id) {
                            var buttonId = 'playFinch_' + playerId + '_button';
                            this.addActionButton(buttonId , _(this.gamedatas.players[playerId].name), this.playFinch.bind(this, cardId, playerId));
                            if (this.playersHandCount[playerId].getValue() < 3){
                                dojo.addClass(buttonId, 'disabled');
                            }
                        }
                    }
                    this.addActionButton('cancelAction_button', _('Cancel'), 'cancelAction');
                    break;

                case "6": // ferret card
                    this.backupDescriptionMyTurn = this.gamedatas.gamestate.descriptionmyturn;
                    this.gamedatas.gamestate.descriptionmyturn = _('Choose a direction to pass cards.');
                    this.updatePageTitle();
                    this.removeActionButtons();
                    this.addActionButton('playFerretLeft_button', _('Left'), this.playFerret.bind(this, cardId, 0));
                    this.addActionButton('playFerretRight_button', _('Right'), this.playFerret.bind(this, cardId, 1));
                    this.addActionButton('cancelAction_button', _('Cancel'), 'cancelAction');
                    break;

                default:
                    this.ajaxAction('playAction', {
                        id: cardId,
                        lock: true
                    });
            }
        },

        isCoyoteSelection: function(items) {
            if (!this.isCoyoteState) {
                return false;
            }
            if (items.length === 0) {
                return false;
            }
            var type = items[0].type;
            return items.every(item => item.type === type);
        },

        addToCollection: function() {
            var items = this.playerHand.getSelectedItems();
            var cardIds = items.map(item => item.id).join(',');

            if (items.length !== 1 && !this.isCoyoteSelection(items)) {
                this.showMessage('Cannot add selected cards to hand', 'error');
                return;
            }

            this.ajaxAction('addToCollection', {
                ids: cardIds,
                lock: true
            });
        },

        discardCards: function() {
            var items = this.playerHand.getSelectedItems();
            var cardIds = items.map(item => item.id).join(',');

            this.ajaxAction('discardCards', {
                ids: cardIds,
                lock: true
            });
        },

        giveCards: function() {
            var items = this.playerHand.getSelectedItems();

            if (items.length !== 2) {
                this.showMessage('Incorrect number of cards selected', 'error');
                return;
            }

            this.ajaxAction('giveCards', {
                target1_id: items[0].id,
                target2_id: items[1].id,
                lock: true
            });
        },

        drawCards: function() {
            this.ajaxAction('drawCards', {
                lock: true
            });
        },

        playFox: function() {
            var foxCard = this.playerHand.getAllItems().find(card => card.type == 4);
            if (!foxCard) {
                this.showMessage('No Fox card to play', 'error');
                return;
            }

            this.ajaxAction('playFox', {
                id: foxCard.id,
                lock: true
            });
        },

        passFox: function() {
            this.ajaxAction('passFox', {
                lock: true
            });
        },

        playCrane: function() {
            var craneCard = this.playerHand.getAllItems().find(card => card.type == 8);
            if (!craneCard) {
                this.showMessage('No Fox card to play', 'error');
                return;
            }

            this.ajaxAction('playCrane', {
                id: craneCard.id,
                lock: true
            });
        },

        passCrane: function() {
            this.ajaxAction('passCrane', {
                lock: true
            });
        },


        ///////////////////////////////////////////////////
        //// Reaction to cometD notifications

        /*
            setupNotifications:

            In this method, you associate each of your game notifications with your local method to handle it.

            Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                  your siegeofcranes.game.php file.

        */
        setupNotifications: function()
        {
            dojo.subscribe('addToCollection', this, "notif_addToCollection");
            dojo.subscribe('swapCollectionCards', this, "notif_swapCollectionCards");
            dojo.subscribe('newScores', this, "notif_newScores");
            dojo.subscribe('drawCards', this, "notif_updateHandCount");
            this.notifqueue.setIgnoreNotificationCheck('drawCards', (notif) => notif.args.player_id == this.player_id);
            dojo.subscribe('playerDrawCards', this, "notif_playerDrawCards");
            dojo.subscribe('discardCards', this, "notif_discardCards");
            dojo.subscribe('playersRotateHand', this, "notif_playersRotateHand");
            dojo.subscribe('playAction', this, "notif_discardCard");
            dojo.subscribe('playFox', this, "notif_discardCard");
            dojo.subscribe('discardCollectedCards', this, "notif_discardCollectedCards");
            dojo.subscribe('giveCards', this, "notif_giveCards");
            this.notifqueue.setIgnoreNotificationCheck('giveCards', (notif) => notif.args.giver_id == this.player_id || notif.args.receiver_id == this.player_id);
            dojo.subscribe('playerGiveCards', this, "notif_playerGiveCards");
            dojo.subscribe('playerReceiveCards', this, "notif_playerReceiveCards");
            dojo.subscribe('shuffleDiscard', this, "notif_shuffleDiscard");
        },

        notif_addToCollection: function(notif) {
            notif.args.card_ids.forEach(cardId => {
                this.addCardToCollection(notif.args.player_id, notif.args.type, cardId);
            });
            this.playersHandCount[notif.args.player_id].setValue(notif.args.card_count);
        },

        notif_swapCollectionCards: function(notif) {
            t1PlayerId = notif.args.target1_player_id;
            t2PlayerId = notif.args.target2_player_id;
            t1cardId = notif.args.target1_card_id;
            t2cardId = notif.args.target2_card_id;
            t1cardType = notif.args.target1_card_type;
            t2cardType = notif.args.target2_card_type;

            this.playersCollection[t1PlayerId].removeFromStockById(t1cardId);
            this.slideTemporaryObject(
                this.format_block(
                    'jstpl_cardontable',
                    {
                        x: this.cardWidth * ((t1cardType - 1) % 5),
                        y: this.cardHeight * Math.floor((t1cardType - 1) / 5),
                        card_id: t1cardId
                    }
                ),
                'playercollection_' + t2PlayerId,
                'playercollection_' + t1PlayerId,
                'playercollection_' + t2PlayerId
            ).play();
            this.playersCollection[t2PlayerId].addToStockWithId(t1cardType, t1cardId);

            this.playersCollection[t2PlayerId].removeFromStockById(t2cardId);
            this.slideTemporaryObject(
                this.format_block(
                    'jstpl_cardontable',
                    {
                        x: this.cardWidth * ((t2cardType - 1) % 5),
                        y: this.cardHeight * Math.floor((t2cardType - 1) / 5),
                        card_id: t2cardId
                    }
                ),
                'playercollection_' + t1PlayerId,
                'playercollection_' + t2PlayerId,
                'playercollection_' + t1PlayerId
            ).play();
            this.playersCollection[t1PlayerId].addToStockWithId(t2cardType, t2cardId);
        },

        notif_newScores: function(notif) {
            for (var playerId in notif.args.newScores) {
                this.scoreCtrl[playerId].toValue(notif.args.newScores[playerId]);
            }
        },

        notif_updateHandCount: function(notif) {
            this.playersHandCount[notif.args.player_id].setValue(notif.args.card_count);
            if (notif.args.second_deck > this.secondDeck || (notif.args.second_deck == this.secondDeck && notif.args.deck_count < this.deckCount.getValue())) {
                this.deckCount.setValue(notif.args.deck_count);
                this.secondDeck = notif.args.second_deck;
            }
            if (notif.args.discard_count) {
                this.discardCount.setValue(notif.args.discard_count);
            }
        },

        notif_giveCards: function(notif) {
            this.playersHandCount[notif.args.giver_id].setValue(notif.args.giver_card_count);
            this.playersHandCount[notif.args.receiver_id].setValue(notif.args.receiver_card_count);
        },

        notif_playerGiveCards: function(notif) {
            for (var card in notif.args.cards) {
                this.giveCard(notif.args.giver_id, notif.args.cards[card].type, notif.args.cards[card].id, 'overall_player_board_' + notif.args.receiver_id);
            }
            this.playersHandCount[notif.args.giver_id].setValue(notif.args.giver_card_count);
            this.playersHandCount[notif.args.receiver_id].setValue(notif.args.receiver_card_count);
        },

        notif_playerReceiveCards: function(notif) {
            this.addCardsToHand(notif.args.cards, 'overall_player_board_' + notif.args.giver_id);
            this.playersHandCount[notif.args.giver_id].setValue(notif.args.giver_card_count);
            this.playersHandCount[notif.args.receiver_id].setValue(notif.args.receiver_card_count);
        },

        notif_playerDrawCards: function(notif) {
            this.addCardsToHand(notif.args.cards);
            this.playersHandCount[notif.args.player_id].setValue(notif.args.card_count);
            if (notif.args.second_deck > this.secondDeck || (notif.args.second_deck == this.secondDeck && notif.args.deck_count < this.deckCount.getValue())) {
                this.deckCount.setValue(notif.args.deck_count);
                this.secondDeck = notif.args.second_deck;
            }
        },

        notif_playersRotateHand: function(notif) {
            console.log('notif_playersRotateHand', notif.args);
            var cards = this.playerHand.getAllItems();
            for (var cardIndex in cards) {
                this.giveCard(notif.args.player_id, cards[cardIndex].type, cards[cardIndex].id, 'overall_player_board_' + notif.args.next_player_id);
            }
            this.addCardsToHand(notif.args.cards, 'overall_player_board_' + notif.args.prev_player_id);

            var playersCardCount = notif.args.players_card_count;
            for (var playerId in playersCardCount) {
                this.playersHandCount[playerId].setValue(playersCardCount[playerId]);
            }
        },

        notif_playerDiscardAndDrawCards: function(notif) {
            var cards = this.playerHand.getAllItems();
            for (var card in cards) {
                this.discardCard(notif.args.player_id, cards[card].type, cards[card].id, notif.args.top_discard_type);
            }
            this.addCardsToHand(notif.args.cards);
            this.playersHandCount[notif.args.player_id].setValue(notif.args.card_count);
            this.deckCount.setValue(notif.args.deck_count);
            this.discardCount.setValue(notif.args.discard_count);
        },

        notif_discardCard: function(notif) {
            this.discardCard(notif.args.player_id, notif.args.type, notif.args.card_id);
            this.playersHandCount[notif.args.player_id].setValue(notif.args.card_count);
            this.discardCount.setValue(notif.args.discard_count);
        },

        notif_discardCards: function(notif) {
            for (var card in notif.args.cards) {
                this.discardCard(notif.args.player_id, notif.args.cards[card].type, notif.args.cards[card].id, notif.args.top_discard_type);
            }
            this.playersHandCount[notif.args.player_id].setValue(notif.args.card_count);
            this.discardCount.setValue(notif.args.discard_count);
        },

        notif_discardCollectedCards: function(notif) {
            console.log('cards', notif.args.cards);
            for (var card in notif.args.cards) {
                this.discardCollectedCard(notif.args.cards[card].location_arg, notif.args.cards[card].type, notif.args.cards[card].id, 'discard', notif.args.top_discard_type);
            }
            this.discardCount.setValue(notif.args.discard_count);
        },

        notif_shuffleDiscard: function(notif) {
            this.deckCount.setValue(this.discardCount.getValue());
            this.discardCount.setValue(0);

            dojo.removeClass('discard', 'cardontable', 'card');
            dojo.style('discard', 'background-position', '');
            this.slideTemporaryObject(
                this.format_block('jstpl_cardback', {id: 1}),
                'deck',
                'discard',
                'deck'
            ).play();
        },
   });
});
