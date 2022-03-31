/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * SiegeOfCranes implementation : © <Your name here> <Your email address here>
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
            this.cardwidth = 100;
            this.cardheight = 143;

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

        setup: function( gamedatas )
        {
            console.log( "Starting game setup" );

            // set up player hand
            this.playerHand = this.setupStock('myhand');

            // Cards in player's hand
            for (var i in this.gamedatas.hand) {
                var card = this.gamedatas.hand[i];
                this.playerHand.addToStockWithId(card.type, card.id);
            }

            // Setting up all players' collections
            this.playersCollection = [];
            for (var player_id in gamedatas.players) {
                // var player = gamedatas.players[player_id];
                this.playersCollection[player_id] = this.setupStock(`playercollection_${player_id}`);
            }

            // Cards played on table
            for (i in this.gamedatas.collections) {
                var card = this.gamedatas.collections[i];
                var player_id = card.location_arg;
                // this.playersCollection[player_id].addToStockWithId(card.type, card.id);
                this.addCardToCollection(player_id, card.type, card.id);
            }

            // dojo.connect(this.playerHand, 'onChangeSelection', this, 'onPlayerHandSelectionChanged');

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

            switch( stateName )
            {

            /* Example:

            case 'myGameState':

                // Show some HTML block at this game state
                dojo.style( 'my_html_block_id', 'display', 'block' );

                break;
           */


            case 'dummmy':
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
                        this.addActionButton('playAction_button', _('Play Action'), 'addToCollection');
                        this.addActionButton('addToCollection_button', _('Add to Collection'), 'addToCollection');
                        this.addActionButton('drawCards_button', _('Draw Cards'), 'drawCards');
                        break;
/*
                 Example:

                 case 'myGameState':

                    // Add 3 action buttons in the action status bar:

                    this.addActionButton( 'button_1_id', _('Button 1 label'), 'onMyMethodToCall1' );
                    this.addActionButton( 'button_2_id', _('Button 2 label'), 'onMyMethodToCall2' );
                    this.addActionButton( 'button_3_id', _('Button 3 label'), 'onMyMethodToCall3' );
                    break;
*/
                }
            }
        },

        ///////////////////////////////////////////////////
        //// Utility methods

        /*

            Here, you can defines some utility methods that you can use everywhere in your javascript
            script.

        */

        setupStock: function(templateName) {
            var stock = new ebg.stock(); // new stock object for hand
            stock.create(this, $(templateName), this.cardwidth, this.cardheight);

            stock.image_items_per_row = 5;

            for (var type = 1; type <= 10; type++) {
                stock.addItemType(type, type, g_gamethemeurl + 'img/cards.jpg', type - 1);
            }

            return stock;
        },

        addCardToCollection : function(player_id, type, card_id) {
            var source_id = 'overall_player_board_' + player_id;
            if ($('myhand_item_' + card_id)) {
                source_id = 'myhand_item_' + card_id;
                this.playerHand.removeFromStockById(card_id);
            }
            this.slideTemporaryObject(
                this.format_block(
                    'jstpl_cardontable',
                    {
                        x: this.cardwidth * ((type - 1) % 5),
                        y: this.cardheight * Math.floor((type - 1) / 5),
                        card_id: card_id
                    }
                ),
                'playercollection_' + player_id,
                source_id,
                'playercollection_' + player_id
            ).play();

            this.playersCollection[player_id].addToStockWithId(type, card_id);
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

        /* Example:

        onMyMethodToCall1: function( evt )
        {
            console.log( 'onMyMethodToCall1' );

            // Preventing default browser reaction
            dojo.stopEvent( evt );

            // Check that this action is possible (see "possibleactions" in states.inc.php)
            if( ! this.checkAction( 'myAction' ) )
            {   return; }

            this.ajaxcall( "/siegeofcranes/siegeofcranes/myAction.html", {
                                                                    lock: true,
                                                                    myArgument1: arg1,
                                                                    myArgument2: arg2,
                                                                    ...
                                                                 },
                         this, function( result ) {

                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)

                         }, function( is_error) {

                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                         } );
        },

        */

        addToCollection: function() {
            var items = this.playerHand.getSelectedItems();
            if (items.length > 0) {
                var action = 'addToCollection';
                if (this.checkAction(action, true)) {
                    var card_id = items[0].id;
                    this.ajaxcall(
                        "/" + this.game_name + "/" +this.game_name + "/" + action + ".html",
                        {
                            id: card_id,
                            lock: true
                        },
                        this,
                        function(result) {},
                        function(is_error) {}
                    );

                    this.playerHand.unselectAll();
                } else {
                    this.playerHand.unselectAll();
                }
            }
        },

        drawCards: function() {
            var action = 'drawCards';
            if (this.checkAction(action, true)) {
                this.ajaxcall(
                    "/" + this.game_name + "/" +this.game_name + "/" + action + ".html",
                    {
                        id: 1,
                        lock: true
                    },
                    this,
                    function(result) {},
                    function(is_error) {}
                );
            }
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
            console.log( 'notifications subscriptions setup' );

            // TODO: here, associate your game notifications with local methods

            // Example 1: standard notification handling
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );

            // Example 2: standard notification handling + tell the user interface to wait
            //            during 3 seconds after calling the method in order to let the players
            //            see what is happening in the game.
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
            // this.notifqueue.setSynchronous( 'cardPlayed', 3000 );
            //

            dojo.subscribe('addToCollection', this, "notif_addToCollection");
            dojo.subscribe('newScores', this, "notif_newScores");
            dojo.subscribe('drawCards', this, "notif_drawCards");
            dojo.subscribe('playerDrawCards', this, "notif_playerDrawCards");
        },

        // TODO: from this point and below, you can write your game notifications handling methods

        /*
        Example:

        notif_cardPlayed: function( notif )
        {
            console.log( 'notif_cardPlayed' );
            console.log( notif );

            // Note: notif.args contains the arguments specified during you "notifyAllPlayers" / "notifyPlayer" PHP call

            // TODO: play the card in the user interface.
        },

        */

        notif_addToCollection: function(notif) {
            this.addCardToCollection(notif.args.player_id, notif.args.type, notif.args.card_id);
        },

        notif_newScores: function(notif) {
            for (var player_id in notif.args.newScores) {
                this.scoreCtrl[player_id].toValue(notif.args.newScores[player_id]);
            }
        },

        notif_drawCards: function(notif) {
            // do nothing
        },

        notif_playerDrawCards: function(notif) {
            for (var card_index in notif.args.cards) {
                var card = notif.args.cards[card_index];
                this.playerHand.addToStockWithId(card.type, card.id);
            }
        },
   });
});
