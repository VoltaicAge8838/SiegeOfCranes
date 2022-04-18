<?php
 /**
  *------
  * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
  * SiegeOfCranes implementation : © <Your name here> <Your email address here>
  *
  * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
  * See http://en.boardgamearena.com/#!doc/Studio for more information.
  * -----
  *
  * siegeofcranes.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */


require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );


class SiegeOfCranes extends Table
{
	function __construct( )
	{
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();

        self::initGameStateLabels( array(
               "target_action_card_id" => 10,
            //    "my_second_global_variable" => 11,
            //      ...
            //    "my_first_game_variant" => 100,
            //    "my_second_game_variant" => 101,
            //      ...
        ) );
        $this->cards = self::getNew("module.common.deck");
        $this->cards->init("card");
	}

    protected function getGameName( )
    {
		// Used for translations and stuff. Please do not modify.
        return "siegeofcranes";
    }

    /*
        setupNewGame:

        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame( $players, $options = array() )
    {
        // Set the colors of the players with HTML color code
        // The default below is red/green/blue/orange/brown
        // The number of colors defined here must correspond to the maximum number of players allowed for the gams
        $gameinfos = self::getGameinfos();
        $default_colors = $gameinfos['player_colors'];

        // Create players
        // Note: if you added some extra field on "player" table in the database (dbmodel.sql), you can initialize it there.
        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ";
        $values = array();
        foreach( $players as $player_id => $player )
        {
            $color = array_shift( $default_colors );
            $values[] = "('".$player_id."','$color','".$player['player_canal']."','".addslashes( $player['player_name'] )."','".addslashes( $player['player_avatar'] )."')";
        }
        $sql .= implode( $values, ',' );
        self::DbQuery( $sql );
        self::reattributeColorsBasedOnPreferences( $players, $gameinfos['player_colors'] );
        self::reloadPlayersBasicInfos();

        /************ Start the game initialization *****/

        // Init global values with their initial values
        //self::setGameStateInitialValue( 'my_first_global_variable', 0 );

        // Init game statistics
        // (note: statistics used in this file must be defined in your stats.inc.php file)
        //self::initStat( 'table', 'table_teststat1', 0 );    // Init a table statistics
        //self::initStat( 'player', 'player_teststat1', 0 );  // Init a player statistics (for all players)

        // TODO: setup the initial game situation here

        // Create cards
        $cards = array();
        foreach ($this->card_types as $type_id => $type) {
            for ($value = 0; $value < 10; $value++) {
                $cards [] = array(
                    'type' => $type_id,
                    'type_arg' => 1,
                    'nbr' => 1
                );
            }
        }
        $this->cards->createCards($cards, 'deck');

        // shuffle deck
        $this->cards->shuffle('deck');
        // deal 13 cards to each player
        $players = self::loadPlayersBasicInfos();
        foreach ($players as $player_id => $player) {
            $cards = $this->cards->pickCards(5, 'deck', $player_id);
        }


        // Activate first player (which is in general a good idea :) )
        $this->activeNextPlayer();

        /************ End of the game initialization *****/
    }

    /*
        getAllDatas:

        Gather all informations about current game situation (visible by the current player).

        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    protected function getAllDatas()
    {
        $result = array();

        $current_player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!

        // Get information about players
        // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
        $sql = "SELECT player_id id, player_score score FROM player ";
        $result['players'] = self::getCollectionFromDb( $sql );

        // TODO: Gather all information about current game situation (visible by player $current_player_id).

        // Cards in player hand
        $result['hand'] = $this->cards->getCardsInLocation('hand', $current_player_id);

        // Cards played on the table
        $result['collections'] = $this->cards->getCardsInLocation('collections');

        return $result;
    }

    /*
        getGameProgression:

        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).

        This method is called each time we are in a game state with the "updateGameProgression" property set to true
        (see states.inc.php)
    */
    function getGameProgression()
    {
        // TODO: compute and return the game progression

        return 0;
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////

    /*
        In this space, you can put any utility methods useful for your game logic
    */


    function scoreForType($n) {
        switch ($n) {
            case 1: return 0;
            case 2: return 1;
            case 3: return 2;
            case 4: return 3;
            case 5: return 5;
            case 6: return 7;
            case 7: return 10;
            case 8: return 14;
            case 9: return 20;
            case 10: return 29;
            default: return 0;
        }
    }

    function updateScores() {
        $players = self::loadPlayersBasicInfos();
        $players_card_count = array();
        foreach ($players as $player_id => $player) {
            $players_card_count[$player_id] = array();
            foreach ($this->card_types as $type_id => $type) {
                $players_card_count[$player_id][$type_id] = 0;
            }
        }

        $cards = $this->cards->getCardsInLocation('collections');
        foreach ($cards as $card_index => $card) {
            $players_card_count[$card['location_arg']][$card['type']]++;
        }

        $players_scores = array();
        foreach ($players_card_count as $player_id => $points) {
            $score = 0;
            foreach($players_card_count[$player_id] as $card_type => $count) {
                $score += $this->scoreForType($count);
            }
            $sql = "UPDATE player SET player_score=$score WHERE player_id='$player_id'";
            self::DbQuery($sql);
            $players_scores[$player_id] = $score;
        }

        self::notifyAllPlayers(
            'newScores',
            '',
            array ('newScores' => $players_scores)
        );
    }

    function drawCards($number_of_cards, $player_id, $player_name) {
        $cards = $this->cards->pickCards($number_of_cards, 'deck', $player_id);
        // and notify
        self::notifyAllPlayers(
            'drawCards',
            clienttranslate('${player_name} draws ${number_of_cards} cards'),
            array (
                'player_id' => $player_id,
                'player_name' => $player_name,
                'number_of_cards' => $number_of_cards
            )
        );
        self::notifyPlayer(
            $player_id,
            'playerDrawCards',
            clienttranslate('${player_name} draws ${number_of_cards} cards'),
            array (
                'player_id' => $player_id,
                'player_name' => $player_name,
                'number_of_cards' => $number_of_cards,
                'cards' => $cards
            )
        );
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
////////////

    /*
        Each time a player is doing some game action, one of the methods below is called.
        (note: each method below must match an input method in siegeofcranes.action.php)
    */

    /*

    Example:

    function playCard( $card_id )
    {
        // Check that this is the player's turn and that it is a "possible action" at this game state (see states.inc.php)
        self::checkAction( 'playCard' );

        $player_id = self::getActivePlayerId();

        // Add your game logic to play a card there
        ...

        // Notify all players about the card played
        self::notifyAllPlayers( "cardPlayed", clienttranslate( '${player_name} plays ${card_name}' ), array(
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName(),
            'card_name' => $card_name,
            'card_id' => $card_id
        ) );

    }

    */

    function playAction($card_id) {
        self::checkAction("playAction");
        $current_card = $this->cards->getCard($card_id);

        if ($this->card_types[$current_card['type']]['react'] == 1) {
            throw new BgaUserException(self::_("Reaction cards may not be played now."));
        }

        $player_id = self::getActivePlayerId();
        $this->cards->moveCard($card_id, 'discard', $player_id);

        // and notify
        self::notifyAllPlayers(
            'playAction',
            clienttranslate('${player_name} plays ${type_displayed}'),
            array (
                'i18n' => array('type_displayed'),
                'card_id' => $card_id,
                'player_id' => $player_id,
                'player_name' => self::getActivePlayerName(),
                'type' => $current_card['type'],
                'type_displayed' => $this->card_types[$current_card['type']]['name']
            )
        );

        self::setGameStateValue("target_action_card_id", $card_id);

        if ($this->card_types[$current_card['type']]['attack'] == 1) {
            $this->gamestate->nextState('waitForFoxes');
        } else {
            $this->gamestate->nextState('playAction');
        }
    }

    function playFox($card_id) {
        self::checkAction("playFox");
        $player_id = self::getCurrentPlayerId();
        $current_card = $this->cards->getCard($card_id);

        // 4 == fox card
        if ($current_card['type'] != 4) {
            throw new BgaUserException(self::_("Only a Fox card may be played now."));
        }

        $target_card_id = self::getGameStateValue("target_action_card_id");
        $target_card = $this->cards->getCard($target_card_id);

        $this->cards->moveCard($card_id, 'discard', $player_id);

        $players = self::loadPlayersBasicInfos();

        self::notifyAllPlayers(
            'playFox',
            clienttranslate('${player_name} plays ${type_displayed} to affect ${target_type_displayed}'),
            array (
                'i18n' => array('type_displayed'),
                'player_id' => $player_id,
                'player_name' => $players[$player_id]['player_name'],
                'card_id' => $card_id,
                'type' => $current_card['type'],
                'type_displayed' => $this->card_types[$current_card['type']]['name'],
                'target_card_id' => $target_card_id,
                'target_type' => $target_card['type'],
                'target_type_displayed' => $this->card_types[$target_card['type']]['name']
            )
        );

        $this->gamestate->nextState('playFox');
    }

    function passFox() {
        self::checkAction("passFox");
        $player_id = self::getCurrentPlayerId();
        $this->gamestate->setPlayerNonMultiactive($player_id, 'passFox');
    }

    function addToCollection($card_id) {
        self::checkAction("addToCollection");
        $player_id = self::getActivePlayerId();
        $this->cards->moveCard($card_id, 'collections', $player_id);
        $currentCard = $this->cards->getCard($card_id);
        // notify collected card
        self::notifyAllPlayers(
            'addToCollection',
            clienttranslate('${player_name} adds ${type_displayed} to their collection'),
            array (
                'i18n' => array('type_displayed'),
                'card_id' => $card_id,
                'player_id' => $player_id,
                'player_name' => self::getActivePlayerName(),
                'type' => $currentCard['type'],
                'type_displayed' => $this->card_types[$currentCard['type']]['name']
            )
        );
        $this->updateScores();
        // next player
        $this->gamestate->nextState('nextPlayer');
    }

    function drawCardsAction() {
        self::checkAction("drawCards");
        $this->drawCards(2, self::getActivePlayerId(), self::getActivePlayerName());

        $this->gamestate->nextState('nextPlayer');
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

    /*
        Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
        These methods function is to return some additional information that is specific to the current
        game state.
    */

    /*

    Example for game state "MyGameState":

    function argMyGameState()
    {
        // Get some values from the current game situation in database...

        // return values:
        return array(
            'variable1' => $value1,
            'variable2' => $value2,
            ...
        );
    }
    */

    function argGiveCards() {
        return array();
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

    /*
        Here, you can create methods defined as "game state actions" (see "action" property in states.inc.php).
        The action method of state X is called everytime the current game state is set to X.
    */

    /*

    Example for game state "MyGameState":

    function stMyGameState()
    {
        // Do some stuff ...

        // (very often) go to another gamestate
        $this->gamestate->nextState( 'some_gamestate_transition' );
    }
    */

    function stNextPlayer() {
        $player_id = self::activeNextPlayer();
        self::giveExtraTime($player_id);
        $this->gamestate->nextState('playerTurn');
    }

    function stPerformAction() {
        $current_player_id = self::getActivePlayerId();
        $card_id = self::getGameStateValue("target_action_card_id");

        $currentCard = $this->cards->getCard($card_id);

        switch ($currentCard['type']) {
            case 1: // rats
                // move to state
                break;
            case 2: // pandas
                break;
            case 3: // kangaroo
                // TODO: move to state
                break;
            case 4: // foxes
                throw new BgaVisibleSystemException ('Attempted to perform action for a Fox card.');
            case 5: // finches
                // TODO: move to state
                break;
            case 6: // ferrets
                break;
            case 7: // crocodiles
                // TODO: move to state
                break;
            case 8: // cranes
                throw new BgaVisibleSystemException ('Attempted to perform action for a Crain card.');
            case 9: // coyotes
                // TODO: move to state
                break;
            case 10: // jays
                $players = self::loadPlayersBasicInfos();
                foreach ($players as $player_id => $player) {
                    $cards_to_draw = 1;
                    $text = '${player_name} draws 1 card';
                    if ($player_id == $current_player_id) {
                        $cards_to_draw = 4;
                        $text = '${player_name} draws 4 cards';
                    }
                    $this->drawCards($cards_to_draw, $player_id, $player['player_name']);
                }
                break;
            default:
                $type = $currentCard['type'];
                throw new BgaVisibleSystemException ("Attempted to perform action for an unknown card type: $type.");
        }

        $this->gamestate->nextState('nextPlayer');
    }

    function stAddToCollection() {
        $this->gamestate->nextState('nextPlayer');
    }

    function stMultiPlayerInit() {
        $this->gamestate->setAllPlayersMultiactive();
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Zombie
////////////

    /*
        zombieTurn:

        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).

        Important: your zombie code will be called when the player leaves the game. This action is triggered
        from the main site and propagated to the gameserver from a server, not from a browser.
        As a consequence, there is no current player associated to this action. In your zombieTurn function,
        you must _never_ use getCurrentPlayerId() or getCurrentPlayerName(), otherwise it will fail with a "Not logged" error message.
    */

    function zombieTurn( $state, $active_player )
    {
    	$statename = $state['name'];

        if ($state['type'] === "activeplayer") {
            switch ($statename) {
                default:
                    $this->gamestate->nextState( "zombiePass" );
                	break;
            }

            return;
        }

        if ($state['type'] === "multipleactiveplayer") {
            // Make sure player is in a non blocking status for role turn
            $this->gamestate->setPlayerNonMultiactive( $active_player, '' );

            return;
        }

        throw new feException( "Zombie mode not supported at this game state: ".$statename );
    }

///////////////////////////////////////////////////////////////////////////////////:
////////// DB upgrade
//////////

    /*
        upgradeTableDb:

        You don't have to care about this until your game has been published on BGA.
        Once your game is on BGA, this method is called everytime the system detects a game running with your old
        Database scheme.
        In this case, if you change your Database scheme, you just have to apply the needed changes in order to
        update the game database and allow the game to continue to run with your new version.

    */

    function upgradeTableDb( $from_version )
    {
        // $from_version is the current version of this game database, in numerical form.
        // For example, if the game was running with a release of your game named "140430-1345",
        // $from_version is equal to 1404301345

        // Example:
//        if( $from_version <= 1404301345 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "ALTER TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        if( $from_version <= 1405061421 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "CREATE TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        // Please add your future database scheme changes here
//
//


    }
}
