<?php
 /**
  *------
  * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
  * SiegeOfCranes implementation : © Brock Turner <brocam@gmail.com>
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
               "ferret_direction" => 11,
               "rat_target_id1" => 12,
               "rat_target_id2" => 13,
               "card_player_id" => 14,
               "target_player_id" => 15,
               "collected_card_length" => 16,
               "collected_card_id0" => 17,
               "collected_card_id1" => 18,
               "collected_card_id2" => 19,
               "collected_card_id3" => 20,
               "collected_card_id4" => 21,
               "collected_card_id5" => 22,
               "collected_card_id6" => 23,
               "collected_card_id7" => 24,
               "collected_card_id8" => 25,
               "collected_card_id9" => 26,
               "second_deck" => 27,
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

        $this->cards->shuffle('deck');

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
        $sql = "SELECT player_id id, player_name name, player_score score FROM player ";
        $result['players'] = self::getCollectionFromDb( $sql );

        // count cards in each player's hand
        foreach ($result['players'] as $playerId => $player) {
            $result['players'][$playerId]['handcount'] = count($this->cards->getCardsInLocation('hand', $playerId));
        }

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

        if ($this->cards->countCardInLocation('deck') == 0) {
            if (self::getGameStateValue('second_deck') == 0) {
                self::setGameStateValue('second_deck', 1);
                $this->cards->moveAllCardsInLocation('discard', 'deck');
                $this->cards->shuffle('deck');

                // finish drawing cards
                if (count($cards) < $number_of_cards) {
                    $cards = array_merge($cards, $this->cards->pickCards($number_of_cards, 'deck', $player_id));
                    // check if we've exhausted the deck again
                    if ($this->cards->countCardInLocation('deck') == 0) {
                        $this->gamestate->nextState('gameEnd');
                    }
                }
            } else {
                $this->gamestate->nextState('gameEnd');
            }
        }

        $card_count = count($this->cards->getCardsInLocation('hand', $player_id));
        self::notifyAllPlayers(
            'drawCards',
            clienttranslate('${player_name} draws ${number_of_cards} cards'),
            array (
                'player_id' => $player_id,
                'player_name' => $player_name,
                'number_of_cards' => $number_of_cards,
                'card_count' => $card_count,
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
                'cards' => $cards,
                'card_count' => $card_count,
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
        $this->cards->moveCard($card_id, 'discard');
        $card_count = count($this->cards->getCardsInLocation('hand', $player_id));

        self::setGameStateValue("target_action_card_id", $card_id);

        self::notifyAllPlayers(
            'playAction',
            clienttranslate('${player_name} plays ${type_displayed}'),
            array (
                'i18n' => array('type_displayed'),
                'card_id' => $card_id,
                'player_id' => $player_id,
                'player_name' => self::getActivePlayerName(),
                'type' => $current_card['type'],
                'type_displayed' => $this->card_types[$current_card['type']]['name'],
                'card_count' => $card_count,
            )
        );

        if ($this->card_types[$current_card['type']]['attack'] == 1) {
            $this->gamestate->nextState('waitForFoxes');
        } else {
            $this->gamestate->nextState('playAction');
        }
    }

    function playFerret($card_id, $direction) {
        self::checkAction("playFerret");
        $current_card = $this->cards->getCard($card_id);

        if ($current_card['type'] != 6) {
            $type = $current_card['type'];
            throw new BgaUserException(self::_("Expected ferret card (type 6) but got type $type instead."));
        }

        if ($direction != 0 && $direction != 1) {
            throw new BgaUserException(self::_("Invalid direction: $direction"));
        }

        $direction_name = $this->direction_names[$direction];
        $player_id = self::getActivePlayerId();
        $this->cards->moveCard($card_id, 'discard');
        $card_count = count($this->cards->getCardsInLocation('hand', $player_id));

        self::setGameStateValue("target_action_card_id", $card_id);
        self::setGameStateValue("ferret_direction", $direction);

        self::notifyAllPlayers(
            'playAction',
            clienttranslate('${player_name} plays ${type_displayed} and will have all players pass their hand to the ${direction}'),
            array (
                'card_id' => $card_id,
                'player_id' => $player_id,
                'player_name' => self::getActivePlayerName(),
                'type' => $current_card['type'],
                'type_displayed' => $this->card_types[$current_card['type']]['name'],
                'direction' => $direction_name,
                'card_count' => $card_count,
            )
        );

        $this->gamestate->nextState('waitForFoxes');
    }

    function playRat($card_id, $target1_id, $target2_id) {
        self::checkAction("playRat");
        $current_card = $this->cards->getCard($card_id);

        if ($current_card['type'] != 1) {
            $type = $current_card['type'];
            throw new BgaUserException(self::_("Expected rat card (type 1) but got type $type instead."));
        }

        $target1_card = $this->cards->getCard($target1_id);
        $target2_card = $this->cards->getCard($target2_id);

        //Invalid cards supplied. collections: 2363182, collections: 2363183

        if ($target1_card['location'] != 'collections'
                || $target2_card['location'] != 'collections'
                || $target1_card['location_arg'] == $target2_card['location_arg']) {
            throw new BgaUserException(self::_("Invalid cards supplied."));
        }

        $player_id = self::getActivePlayerId();
        $players = self::loadPlayersBasicInfos();
        $this->cards->moveCard($card_id, 'discard');
        $card_count = count($this->cards->getCardsInLocation('hand', $player_id));

        self::setGameStateValue("target_action_card_id", $card_id);
        self::setGameStateValue("rat_target_id1", $target1_id);
        self::setGameStateValue("rat_target_id2", $target2_id);

        self::notifyAllPlayers(
            'playAction',
            clienttranslate('${player_name} plays ${card_name} and will swap ${target1_player_name}\'s ${target1_card_name} with ${target2_player_name}\'s ${target2_card_name}'),
            array (
                'player_id' => $player_id,
                'type' => $current_card['type'],
                'card_id' => $card_id,
                'player_name' => self::getActivePlayerName(),
                'card_name' => $this->card_types[$current_card['type']]['name'],
                'target1_player_name' => $players[$target1_card['location_arg']]['player_name'],
                'target1_card_name' => $this->card_types[$target1_card['type']]['name'],
                'target2_player_name' => $players[$target2_card['location_arg']]['player_name'],
                'target2_card_name' => $this->card_types[$target2_card['type']]['name'],
                'card_count' => $card_count,
            )
        );

        $this->gamestate->nextState('waitForFoxes');
    }

    function playFinch($card_id, $giver_id) {
        self::checkAction("playFinch");
        $current_card = $this->cards->getCard($card_id);

        if ($current_card['type'] != 5) {
            $type = $current_card['type'];
            throw new BgaUserException(self::_("Expected finch card (type 5) but got type $type instead."));
        }

        $player_id = self::getActivePlayerId();
        $players = self::loadPlayersBasicInfos();
        $this->cards->moveCard($card_id, 'discard');
        $card_count = count($this->cards->getCardsInLocation('hand', $player_id));

        self::setGameStateValue("target_action_card_id", $card_id);
        self::setGameStateValue("card_player_id", $player_id);
        self::setGameStateValue("target_player_id", $giver_id);

        self::notifyAllPlayers(
            'playAction',
            clienttranslate('${player_name} plays ${card_name} to receive two cards from ${target_player_name}'),
            array (
                'player_id' => $player_id,
                'type' => $current_card['type'],
                'card_id' => $card_id,
                'player_name' => self::getActivePlayerName(),
                'card_name' => $this->card_types[$current_card['type']]['name'],
                'target_player_name' => $players[$giver_id]['player_name'],
                'card_count' => $card_count,
            )
        );

        $this->gamestate->nextState('waitForFoxes');
    }

    function giveCards($target1_id, $target2_id) {
        self::checkAction("giveCards");

        // TODO: check that target cards belong to the giver

        $receiver_id = self::getGameStateValue("card_player_id");
        $giver_id = self::getGameStateValue("target_player_id");
        $players = self::loadPlayersBasicInfos();

        $this->cards->moveCard($target1_id, 'hand', $receiver_id);
        $this->cards->moveCard($target2_id, 'hand', $receiver_id);

        $receiver_card_count = count($this->cards->getCardsInLocation('hand', $receiver_id));
        $giver_card_count = count($this->cards->getCardsInLocation('hand', $giver_id));

        self::notifyAllPlayers(
            'giveCards',
            clienttranslate('${giver_name} gives two cards to ${receiver_name}'),
            array (
                'giver_id' => $giver_id,
                'giver_name' => $players[$giver_id]['player_name'],
                'giver_card_count' => $giver_card_count,
                'receiver_id' => $receiver_id,
                'receiver_name' => $players[$receiver_id]['player_name'],
                'receiver_card_count' => $receiver_card_count,
            )
        );

        $target1_card = $this->cards->getCard($target1_id);
        $target2_card = $this->cards->getCard($target2_id);

        self::notifyPlayer(
            $giver_id,
            'playerGiveCards',
            clienttranslate('${giver_name} gives two cards to ${receiver_name}'),
            array (
                'giver_id' => $giver_id,
                'giver_name' => $players[$giver_id]['player_name'],
                'giver_card_count' => $giver_card_count,
                'receiver_id' => $receiver_id,
                'receiver_name' => $players[$receiver_id]['player_name'],
                'receiver_card_count' => $receiver_card_count,
                'cards' => array($target1_card, $target2_card)
            )
        );

        self::notifyPlayer(
            $receiver_id,
            'playerReceiveCards',
            clienttranslate('${giver_name} gives two cards to ${receiver_name}'),
            array (
                'giver_id' => $giver_id,
                'giver_name' => $players[$giver_id]['player_name'],
                'giver_card_count' => $giver_card_count,
                'receiver_id' => $receiver_id,
                'receiver_name' => $players[$receiver_id]['player_name'],
                'receiver_card_count' => $receiver_card_count,
                'cards' => array($target1_card, $target2_card)
            )
        );

        $this->gamestate->nextState('nextPlayer');
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

        $this->cards->moveCard($card_id, 'discard');

        $card_count = count($this->cards->getCardsInLocation('hand', $player_id));
        $players = self::loadPlayersBasicInfos();

        self::notifyAllPlayers(
            'playFox',
            clienttranslate('${player_name} plays ${type_displayed} to affect ${target_type_displayed}'),
            array (
                'player_id' => $player_id,
                'player_name' => $players[$player_id]['player_name'],
                'card_id' => $card_id,
                'type' => $current_card['type'],
                'type_displayed' => $this->card_types[$current_card['type']]['name'],
                'target_card_id' => $target_card_id,
                'target_type' => $target_card['type'],
                'target_type_displayed' => $this->card_types[$target_card['type']]['name'],
                'card_count' => $card_count,
            )
        );

        $this->gamestate->nextState('playFox');
    }

    function passFox() {
        self::checkAction("passFox");
        $player_id = self::getCurrentPlayerId();
        $this->gamestate->setPlayerNonMultiactive($player_id, 'passFox');
    }

    function playCrane($card_id) {
        self::checkAction("playCrane");
        $player_id = self::getCurrentPlayerId();
        $current_card = $this->cards->getCard($card_id);

        // 8 == crane card
        if ($current_card['type'] != 8) {
            throw new BgaUserException(self::_("Only a Crane card may be played now."));
        }

        self::setGameStateValue("target_action_card_id", $card_id);

        $this->cards->moveCard($card_id, 'discard');

        $card_count = count($this->cards->getCardsInLocation('hand', $player_id));
        $players = self::loadPlayersBasicInfos();

        self::notifyAllPlayers(
            'playAction',
            clienttranslate('${player_name} plays ${type_displayed} to discard the last collected cards'),
            array (
                'player_id' => $player_id,
                'player_name' => $players[$player_id]['player_name'],
                'card_id' => $card_id,
                'type' => $current_card['type'],
                'type_displayed' => $this->card_types[$current_card['type']]['name'],
                'card_count' => $card_count,
            )
        );

        $this->gamestate->nextState('playCrane');
    }

    function passCrane() {
        self::checkAction("passCrane");
        $player_id = self::getCurrentPlayerId();
        $this->gamestate->setPlayerNonMultiactive($player_id, 'passCrane');
    }

    function addToCollection($card_ids) {
        self::checkAction("addToCollection");
        $player_id = self::getActivePlayerId();

        $cardType = $this->cards->getCard($card_ids[array_key_first($card_ids)])['type'];
        foreach ($card_ids as $id) {
            if ($cardType != $this->cards->getCard($id)['type']) {
                throw new BgaUserException(self::_("Collected cards must all be of the same type."));
            }
        }

        $length = count($card_ids);
        if ($length > 10) {
            throw new BgaVisibleSystemException(self::_("Unexpected number of cards to collect: $length"));
        }

        self::setGameStateValue("collected_card_length", $length);
        $index = 0;
        foreach ($card_ids as $id) {
            self::setGameStateValue("collected_card_id$index", $id);
            $index++;
            $this->cards->moveCard($id, 'collections', $player_id);
        }
        $card_count = count($this->cards->getCardsInLocation('hand', $player_id));

        // notify collected card
        self::notifyAllPlayers(
            'addToCollection',
            clienttranslate('${player_name} adds ${type_displayed} to their collection'),
            array (
                'card_ids' => $card_ids,
                'player_id' => $player_id,
                'player_name' => self::getActivePlayerName(),
                'type' => $cardType,
                'type_displayed' => $this->card_types[$cardType]['name'],
                'card_count' => $card_count,
            )
        );
        $this->updateScores();
        $this->gamestate->nextState('addToCollection');
    }

    function discardKangarooCards($card_ids) {
        self::checkAction("discardCards");
        $player_id = self::getCurrentPlayerId();
        $player_cards = $this->cards->getCardsInLocation('hand', $player_id);

        $discard_cards = array();
        $types = array();

        foreach ($card_ids as $key => $id) {
            if (array_key_exists($id, $player_cards)) {
                array_push($discard_cards, array('id'=>$id, 'type'=>$player_cards[$id]['type']));
                array_push($types, $player_cards[$id]['type']);
                unset($player_cards[$id]);
            } else {
                unset($card_ids[$key]);
            }
        }

        if (3 < count($player_cards)) {
            throw new BgaUserException(self::_("Not enough cards selected to discard."));
        }

        foreach ($card_ids as $id) {
            $this->cards->moveCard($id, 'discard');
        }
        $card_count = count($this->cards->getCardsInLocation('hand', $player_id));

        $types_string = implode(', ', $types);

        self::notifyAllPlayers(
            'discardKangarooCards',
            clienttranslate('${player_name} discards ${types_string}'),
            array (
                'cards' => $discard_cards,
                'player_id' => $player_id,
                'player_name' => self::getCurrentPlayerName(),
                'types_string' => $types_string,
                'card_count' => $card_count,
            )
        );

        $this->gamestate->setPlayerNonMultiactive($player_id, 'nextPlayer');
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
        $current_player_name = self::getActivePlayerName();
        $card_id = self::getGameStateValue("target_action_card_id");

        $currentCard = $this->cards->getCard($card_id);

        switch ($currentCard['type']) {
            case 1: // rats
                $target1_id = self::getGameStateValue("rat_target_id1");
                $target2_id = self::getGameStateValue("rat_target_id2");
                $target1_card = $this->cards->getCard($target1_id);
                $target2_card = $this->cards->getCard($target2_id);
                $player1_id = $target1_card['location_arg'];
                $player2_id = $target2_card['location_arg'];

                $this->cards->moveCard($target1_id, 'collections', $player2_id);
                $this->cards->moveCard($target2_id, 'collections', $player1_id);

                $players = self::loadPlayersBasicInfos();

                self::notifyAllPlayers(
                    'swapCollectionCards',
                    clienttranslate('${target1_player_name}\'s ${target1_card_name} swapped with ${target2_player_name}\'s ${target2_card_name}'),
                    array (
                        'target1_player_name' => $players[$player1_id]['player_name'],
                        'target1_player_id' => $player1_id,
                        'target1_card_name' => $this->card_types[$target1_card['type']]['name'],
                        'target1_card_id' => $target1_id,
                        'target1_card_type' => $target1_card['type'],
                        'target2_player_name' => $players[$player2_id]['player_name'],
                        'target2_player_id' => $player2_id,
                        'target2_card_name' => $this->card_types[$target2_card['type']]['name'],
                        'target2_card_id' => $target2_id,
                        'target2_card_type' => $target2_card['type']
                    )
                );

                $this->updateScores();
                $this->gamestate->nextState('nextPlayer');
                break;
            case 2: // pandas
                $this->cards->moveAllCardsInLocation('hand', 'discard', $current_player_id);
                $cards = $this->cards->pickCards(5, 'deck', $current_player_id);
                $card_count = count($this->cards->getCardsInLocation('hand', $current_player_id));
                self::notifyAllPlayers(
                    'discardAndDrawCards',
                    clienttranslate('${player_name} discards their hand and draws 5 cards'),
                    array (
                        'player_id' => $current_player_id,
                        'player_name' => $current_player_name,
                        'card_count' => $card_count,
                    )
                );
                self::notifyPlayer(
                    $current_player_id,
                    'playerDiscardAndDrawCards',
                    clienttranslate('${player_name} discards their hand and draws 5 cards'),
                    array (
                        'player_id' => $current_player_id,
                        'player_name' => $current_player_name,
                        'cards' => $cards,
                        'card_count' => $card_count,
                    )
                );
                $this->gamestate->nextState('nextPlayer');
                break;
            case 3: // kangaroo
                $this->drawCards(2, $current_player_id, $current_player_name);
                self::setGameStateValue("card_player_id", $current_player_id);
                $this->gamestate->nextState('kangarooDiscard');
                break;
            case 4: // foxes
                throw new BgaVisibleSystemException ('Attempted to perform action for a Fox card.');
            case 5: // finches
                $this->gamestate->nextState('giveCards');
                break;
            case 6: // ferrets
                $direction = self::getGameStateValue("ferret_direction");
                $players = self::loadPlayersBasicInfos();

                // rotate hands
                if (count($players) == 2) {
                    $first_player_id = array_key_first($players);
                    $last_player_id = array_key_last($players);
                    $this->cards->moveAllCardsInLocation('hand', 'temp', $first_player_id);
                    $this->cards->moveAllCardsInLocation('hand', 'hand', $last_player_id, $first_player_id);
                    $this->cards->moveAllCardsInLocation('temp', 'hand', null, $last_player_id);
                } else {
                    if ($direction == 1) {
                        $players = array_reverse($players, true);
                    }
                    $prev_player_id = array_key_last($players);

                    foreach ($players as $player_id => $player) {
                        $this->cards->moveAllCardsInLocation('hand', 'temp', $prev_player_id);
                        $this->cards->moveAllCardsInLocation('hand', 'hand', $player_id, $prev_player_id);
                        $this->cards->moveAllCardsInLocation('temp', 'hand', null, $player_id);
                    }
                }

                // send notifications
                $players_card_count = array();
                foreach ($players as $playerId => $player) {
                    $players_card_count[$playerId] = count($this->cards->getCardsInLocation('hand', $playerId));
                }

                $direction_name = $this->direction_names[$direction];
                $player_ids_except_last = array_slice(array_keys($players), 0, -1);
                $prev_player_id = $player_ids_except_last[array_key_last($player_ids_except_last)];
                $player_id = array_key_last($players);
                foreach ($players as $next_player_id => $player) {
                    $cards = $this->cards->getCardsInLocation('hand', $player_id);

                    self::notifyPlayer(
                        $player_id,
                        'playersRotateHand',
                        clienttranslate('All players pass their hand to the ${direction_name}'),
                        array (
                            'player_id' => $player_id,
                            'cards' => $cards,
                            'direction_name' => $direction_name,
                            'players_card_count' => $players_card_count,
                            'prev_player_id' => $prev_player_id,
                            'next_player_id' => $next_player_id,
                        )
                    );

                    $prev_player_id = $player_id;
                    $player_id = $next_player_id;
                }
                $this->gamestate->nextState('nextPlayer');
                break;
            case 7: // crocodiles
                $this->drawCards(2, $current_player_id, $current_player_name);
                $this->gamestate->nextState('selectCardToCollect');
                break;
            case 8: // cranes
                $length = self::getGameStateValue("collected_card_length");
                $cards = array();
                for ($index = 0; $index < $length; $index++) {
                    $id = self::getGameStateValue("collected_card_id$index");
                    array_push($cards, $this->cards->getCard($id));
                    $this->cards->moveCard($id, 'discard');
                }

                self::notifyAllPlayers(
                    'discardCollectedCards',
                    clienttranslate('${player_name} discards the last collected cards'),
                    array (
                        'player_id' => $current_player_id,
                        'player_name' => $current_player_name,
                        'cards' => $cards,
                    )
                );
                $this->updateScores();
                $this->gamestate->nextState('nextPlayer');
                break;
            case 9: // coyotes
                $this->gamestate->nextState('selectMultipleCardsToCollect');
                break;
            case 10: // jays
                $players = self::loadPlayersBasicInfos();
                foreach ($players as $player_id => $player) {
                    $cards_to_draw = 1;
                    if ($player_id == $current_player_id) {
                        $cards_to_draw = 4;
                    }
                    $this->drawCards($cards_to_draw, $player_id, $player['player_name']);
                }
                $this->gamestate->nextState('nextPlayer');
                break;
            default:
                $type = $currentCard['type'];
                throw new BgaVisibleSystemException ("Attempted to perform action for an unknown card type: $type.");
        }
    }

    function stAllOtherPlayersInit() {
        // $this->gamestate->setAllPlayersMultiactive();
        $players = self::loadPlayersBasicInfos();
        unset($players[self::getCurrentPlayerId()]);
        $this->gamestate->setPlayersMultiactive(array_keys($players), 'none', true);
    }

    function stAllNonkangarooPlayersInit() {
        $players = self::loadPlayersBasicInfos();
        unset($players[self::getGameStateValue("card_player_id")]);
        $this->gamestate->setPlayersMultiactive(array_keys($players), 'none', true);
    }

    function stFinchPlayerInit() {
        $this->gamestate->setPlayersMultiactive(array(self::getGameStateValue("target_player_id")), 'none', true);
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
