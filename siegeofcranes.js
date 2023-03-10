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
            this.cardWidth = 160;
            this.cardHeight = 250;
            this.iconWidth = 64;
            this.iconHeight = 64;
            this.backupDescriptionMyTurn = '';
            this.isCoyoteState = true;

            this.passButtonText = _('Pass');
            this.attackCardText = _('Attack Card');
            this.reactCardText = _('Reaction');
            this.cancelText = _('Cancel');
            this.playActionText = _('Play Action');
            this.addToCollectionText = _('Add to Collection');
            this.drawCardsText = _('Draw Cards');
            this.discardCardsText = _('Discard Cards');
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
            this.playerHand = this.setupStock('myhand', 'cards_blank.jpg', this.cardWidth, this.cardHeight);
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
                $('discard').innerHTML = this.cardHtmlBlock(type, 'inner_discard');
            }

            // setup button timers
            var intervalId;
            var countDown;

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

                case 'waitForRatUndoFoxes':
                case 'waitForKangarooUndoFoxes':
                case 'waitForFinchUndoFoxes':
                case 'waitForFerretUndoFoxes':
                case 'waitForCraneUndoFoxes':
                case 'waitForRatRedoFoxes':
                case 'waitForKangarooRedoFoxes':
                case 'waitForFinchRedoFoxes':
                case 'waitForFerretRedoFoxes':
                case 'waitForCraneRedoFoxes':
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
                        this.addActionButton('playAction_button', this.playActionText, 'playAction');
                        this.addActionButton('addToCollection_button', this.addToCollectionText, 'addToCollection');
                        this.addActionButton('drawCards_button', this.drawCardsText, 'drawCards');

                        dojo.addClass('playAction_button', 'disabled');
                        dojo.addClass('addToCollection_button', 'disabled');
                        break;

                    case 'waitForRatUndoFoxes':
                    case 'waitForKangarooUndoFoxes':
                    case 'waitForFinchUndoFoxes':
                    case 'waitForFerretUndoFoxes':
                    case 'waitForCraneUndoFoxes':
                        if (this.playerHand.getAllItems().find(card => card.type == 4)) {
                            this.addActionButton('playFox_button', _('Cancel Attack'), 'playFox');
                            this.addActionButton('passFox_button', this.passButtonText, 'passFox');
                        } else {
                            clearInterval(this.intervalId);
                            this.countDown = 3;
                            this.addActionButton('passFox_button', `${this.passButtonText} (${this.countDown})`, 'passFox');
                            this.intervalId = setInterval(() => {
                                this.countDown -= 1;
                                if (!$('passFox_button')) {
                                    clearInterval(this.intervalId);
                                    return;
                                }
                                $('passFox_button').textContent = `${this.passButtonText} (${this.countDown})`;
                                if (this.countDown <= 0) {
                                    clearInterval(this.intervalId);
                                    this.passFox();
                                }
                            }, 1000);
                        }
                        break;

                    case 'waitForRatRedoFoxes':
                    case 'waitForKangarooRedoFoxes':
                    case 'waitForFinchRedoFoxes':
                    case 'waitForFerretRedoFoxes':
                    case 'waitForCraneRedoFoxes':
                        if (this.playerHand.getAllItems().find(card => card.type == 4)) {
                            this.addActionButton('playFox_button', _('Permit Attack'), 'playFox');
                            this.addActionButton('passFox_button', this.passButtonText, 'passFox');
                        } else {
                            clearInterval(this.intervalId);
                            this.countDown = 3;
                            this.addActionButton('passFox_button', `${this.passButtonText} (${this.countDown})`, 'passFox');
                            this.intervalId = setInterval(() => {
                                this.countDown -= 1;
                                if (!$('passFox_button')) {
                                    clearInterval(this.intervalId);
                                    return;
                                }
                                $('passFox_button').textContent = `${this.passButtonText} (${this.countDown})`;
                                if (this.countDown <= 0) {
                                    clearInterval(this.intervalId);
                                    this.passFox();
                                }
                            }, 1000);
                        }
                        break;

                    case 'selectMultipleCardsToCollect':
                        this.isCoyoteState = true;
                    case 'selectCardToCollect':
                        this.addActionButton('addToCollection_button', this.addToCollectionText, 'addToCollection');
                        break;

                    case 'kangarooDiscard':
                        this.addActionButton('discardCards_button', this.discardCardsText, 'discardCards');
                        break;

                    case 'giveCards':
                        this.addActionButton('giveCards_button', _('Give Cards'), 'giveCards');
                        break;

                    case 'waitForCranes':
                        if (this.playerHand.getAllItems().find(card => card.type == 8)) {
                            this.addActionButton('playCrane_button', this.discardCardsText, 'playCrane');
                            this.addActionButton('passCrane_button', this.passButtonText, 'passCrane');
                        } else {
                            clearInterval(this.intervalId);
                            this.countDown = 3;
                            this.addActionButton('passCrane_button', `${this.passButtonText} (${this.countDown})`, 'passCrane');
                            this.intervalId = setInterval(() => {
                                this.countDown -= 1;
                                if (!$('passCrane_button')) {
                                    clearInterval(this.intervalId);
                                    return;
                                }
                                $('passCrane_button').textContent = `${this.passButtonText} (${this.countDown})`;
                                if (this.countDown <= 0) {
                                    this.passCrane();
                                }
                            }, 1000);
                        }
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

        setupHandCard: function(cardDiv, cardTypeId, cardId) {
            var title = this.gamedatas.cardTypes[cardTypeId]['name'];
            var attack = this.gamedatas.cardTypes[cardTypeId]['attack'] ? `<strong>${this.attackCardText}</strong><br>` : '';
            var react = this.gamedatas.cardTypes[cardTypeId]['react'] ? `<strong>${this.reactCardText}:</strong> ` : '';
            var description = this.gamedatas.cardTypes[cardTypeId]['description'];
            cardDiv.innerHTML = `<span class="cardtitle">${title}</span><span class="carddescription">${attack}${react}${description}</span>`;
        },

        cardHtmlBlock: function(cardType, cardId) {
            var title = this.gamedatas.cardTypes[cardType]['name'];
            var attack = this.gamedatas.cardTypes[cardType]['attack'] ? `<strong>${this.attackCardText}</strong><br>` : '';
            var react = this.gamedatas.cardTypes[cardType]['react'] ? `<strong>${this.reactCardText}:</strong> ` : '';
            var description = this.gamedatas.cardTypes[cardType]['description'];

            return this.format_block(
                'jstpl_cardontable',
                {
                    x: this.cardTypeX(cardType),
                    y: this.cardTypeY(cardType),
                    card_id: cardId + 't',
                    title,
                    attack,
                    react,
                    description
                }
            );
        },

        moveCardAnimation: function(source, destination, cardType, cardId) {
            return this.slideTemporaryObject(
                this.cardHtmlBlock(cardType, cardId),
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
                {lock: true, ...data},
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
            $('discard').innerHTML = this.cardHtmlBlock(cardType, 'inner_discard');
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
                direction: direction
            });
        },

        playFinch: function(cardId, giverId) {
            this.ajaxAction('playFinch', {
                id: cardId,
                giver_id: giverId
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
                target2_id: targets[1].id
            });

            for (var playerId in this.gamedatas.players) {
                this.playersCollection[playerId].setSelectionMode(0);
            }
        },

        cancelAction: function() {
            this.gamedatas.gamestate.descriptionmyturn = this.backupDescriptionMyTurn;
            this.updatePageTitle();
            this.removeActionButtons();
            this.addActionButton('playAction_button', this.playActionText, 'playAction');
            this.addActionButton('addToCollection_button', this.addToCollectionText, 'addToCollection');
            this.addActionButton('drawCards_button', this.drawCardsText, 'drawCards');
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
                    this.addActionButton('cancelAction_button', this.cancelText, 'cancelAction');

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
                            this.addActionButton(buttonId , this.gamedatas.players[playerId].name, this.playFinch.bind(this, cardId, playerId));
                            if (this.playersHandCount[playerId].getValue() < 3){
                                dojo.addClass(buttonId, 'disabled');
                            }
                        }
                    }
                    this.addActionButton('cancelAction_button', this.cancelText, 'cancelAction');
                    break;

                case "6": // ferret card
                    this.backupDescriptionMyTurn = this.gamedatas.gamestate.descriptionmyturn;
                    this.gamedatas.gamestate.descriptionmyturn = _('Choose a direction to pass cards.');
                    this.updatePageTitle();
                    this.removeActionButtons();
                    this.addActionButton('playFerretLeft_button', _('Left'), this.playFerret.bind(this, cardId, 0));
                    this.addActionButton('playFerretRight_button', _('Right'), this.playFerret.bind(this, cardId, 1));
                    this.addActionButton('cancelAction_button', this.cancelText, 'cancelAction');
                    break;

                default:
                    this.ajaxAction('playAction', {
                        id: cardId
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
                ids: cardIds
            });
        },

        discardCards: function() {
            var items = this.playerHand.getSelectedItems();
            var cardIds = items.map(item => item.id).join(',');

            this.ajaxAction('discardCards', {
                ids: cardIds
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
                target2_id: items[1].id
            });
        },

        drawCards: function() {
            this.ajaxAction('drawCards', {});
        },

        playFox: function() {
            var foxCard = this.playerHand.getAllItems().find(card => card.type == 4);
            if (!foxCard) {
                this.showMessage('No Fox card to play', 'error');
                return;
            }

            this.ajaxAction('playFox', {
                id: foxCard.id
            });
        },

        passFox: function() {
            clearInterval(this.intervalId);
            this.ajaxAction('passFox', {});
        },

        playCrane: function() {
            var craneCard = this.playerHand.getAllItems().find(card => card.type == 8);
            if (!craneCard) {
                this.showMessage('No Fox card to play', 'error');
                return;
            }

            this.ajaxAction('playCrane', {
                id: craneCard.id
            });
        },

        passCrane: function() {
            clearInterval(this.intervalId);
            this.ajaxAction('passCrane', {});
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
            this.notifqueue.setSynchronous( 'playAction', 500 )
            dojo.subscribe('playFox', this, "notif_discardCard");
            this.notifqueue.setSynchronous( 'playFox', 500 )
            dojo.subscribe('discardCollectedCards', this, "notif_discardCollectedCards");
            dojo.subscribe('giveCards', this, "notif_giveCards");
            this.notifqueue.setIgnoreNotificationCheck('giveCards', (notif) => notif.args.giver_id == this.player_id || notif.args.receiver_id == this.player_id);
            dojo.subscribe('playerGiveCards', this, "notif_playerGiveCards");
            dojo.subscribe('playerReceiveCards', this, "notif_playerReceiveCards");
            dojo.subscribe('shuffleDiscard', this, "notif_shuffleDiscard");
            this.notifqueue.setSynchronous( 'shuffleDiscard', 500 )
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
                this.cardHtmlBlock(t1cardType, t1cardId),
                'playercollection_' + t2PlayerId,
                'playercollection_' + t1PlayerId,
                'playercollection_' + t2PlayerId
            ).play();
            this.playersCollection[t2PlayerId].addToStockWithId(t1cardType, t1cardId);

            this.playersCollection[t2PlayerId].removeFromStockById(t2cardId);
            this.slideTemporaryObject(
                this.cardHtmlBlock(t2cardType, t2cardId),
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
            this.playersHandCount[notif.args.player_id].setValue(notif.args.hand_count);
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
            this.playersHandCount[notif.args.player_id].setValue(notif.args.hand_count);
            if (notif.args.second_deck > this.secondDeck || (notif.args.second_deck == this.secondDeck && notif.args.deck_count < this.deckCount.getValue())) {
                this.deckCount.setValue(notif.args.deck_count);
                this.secondDeck = notif.args.second_deck;
            }
        },

        notif_playersRotateHand: function(notif) {
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

        notif_discardCard: function(notif) {
            this.discardCard(notif.args.player_id, notif.args.type, notif.args.card_id);
            this.playersHandCount[notif.args.player_id].setValue(notif.args.hand_count);
            this.discardCount.setValue(notif.args.discard_count);
        },

        notif_discardCards: function(notif) {
            for (var card in notif.args.cards) {
                this.discardCard(notif.args.player_id, notif.args.cards[card].type, notif.args.cards[card].id, notif.args.top_discard_type);
            }
            this.playersHandCount[notif.args.player_id].setValue(notif.args.hand_count);
            this.discardCount.setValue(notif.args.discard_count);
        },

        notif_discardCollectedCards: function(notif) {
            for (var card in notif.args.cards) {
                this.discardCollectedCard(notif.args.cards[card].location_arg, notif.args.cards[card].type, notif.args.cards[card].id, 'discard', notif.args.top_discard_type);
            }
            this.discardCount.setValue(notif.args.discard_count);
        },

        notif_shuffleDiscard: function(notif) {
            this.deckCount.setValue(this.discardCount.getValue());
            this.discardCount.setValue(0);

            dojo.removeClass('discard', 'cardontable');
            dojo.removeClass('discard', 'card');
            dojo.style('discard', 'background-position', '');
            $('discard').innerHTML = '';
            this.slideTemporaryObject(
                this.format_block('jstpl_cardback', {id: 1}),
                'deck',
                'discard',
                'deck'
            ).play();
        },
   });
});
