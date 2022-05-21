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
 * states.inc.php
 *
 * SiegeOfCranes game states description
 *
 */

/*
   Game state machine is a tool used to facilitate game developpement by doing common stuff that can be set up
   in a very easy way from this configuration file.

   Please check the BGA Studio presentation about game state to understand this, and associated documentation.

   Summary:

   States types:
   _ activeplayer: in this type of state, we expect some action from the active player.
   _ multipleactiveplayer: in this type of state, we expect some action from multiple players (the active players)
   _ game: this is an intermediary state where we don't expect any actions from players. Your game logic must decide what is the next game state.
   _ manager: special type for initial and final state

   Arguments of game states:
   _ name: the name of the GameState, in order you can recognize it on your own code.
   _ description: the description of the current game state is always displayed in the action status bar on
                  the top of the game. Most of the time this is useless for game state with "game" type.
   _ descriptionmyturn: the description of the current game state when it's your turn.
   _ type: defines the type of game states (activeplayer / multipleactiveplayer / game / manager)
   _ action: name of the method to call when this game state become the current game state. Usually, the
             action method is prefixed by "st" (ex: "stMyGameStateName").
   _ possibleactions: array that specify possible player actions on this step. It allows you to use "checkAction"
                      method on both client side (Javacript: this.checkAction) and server side (PHP: self::checkAction).
   _ transitions: the transitions are the possible paths to go from a game state to another. You must name
                  transitions in order to use transition names in "nextState" PHP method, and use IDs to
                  specify the next game state for each transition.
   _ args: name of the method to call to retrieve arguments for this gamestate. Arguments are sent to the
           client side to be used on "onEnteringState" or to set arguments in the gamestate description.
   _ updateGameProgression: when specified, the game progression is updated (=> call to your getGameProgression
                            method).
*/

//    !! It is not a good idea to modify this file when a game is running !!


$machinestates = array(

    // The initial state. Please do not modify.
    1 => array(
        "name" => "gameSetup",
        "description" => "",
        "type" => "manager",
        "action" => "stGameSetup",
        "transitions" => array( "" => 2 )
    ),

    // Note: ID=2 => your first state

    2 => array(
        "name" => "playerTurn",
        "description" => clienttranslate('${actplayer} must play a card or pass'),
        "descriptionmyturn" => clienttranslate('${you} must play a card or pass'),
        "type" => "activeplayer",
        "possibleactions" => array("playAction", "playFerret", "playRat", "startCollecting", "addToCollection", "drawCards"),
        "transitions" => array(
            "playAction" => 4,
            "waitForFoxes" => 5,
            "addToCollection" => 7,
            "nextPlayer" => 3
        )
    ),

    3 => array(
        "name" => "nextPlayer",
        "description" => "",
        "type" => "game",
        "action" => "stNextPlayer",
        "transitions" => array(
            "playerTurn" => 2
        )
    ),

    4 => array(
        "name" => "playAction",
        "description" => "",
        "type" => "game",
        "action" => "stPerformAction",
        "transitions" => array(
            "nextPlayer" => 3,
            "selectMultipleCardsToCollect" => 8,
            "selectCardToCollect" => 9,
            "kangarooDiscard" => 10,
        )
    ),

    5 => array(
        "name" => "waitForUndoFoxes",
        "description" => clienttranslate('Other players may play a Fox to cancel the action or pass'),
        "descriptionmyturn" => clienttranslate('${you} may play a Fox to cancel the action or pass'),
        "type" => "multipleactiveplayer",
        "possibleactions" => array("playFox", "passFox"),
        "transitions" => array(
            "playFox" => 6,
            "passFox" => 4
        ),
        "action" => "stAllOtherPlayersInit"
    ),

    6 => array(
        "name" => "waitForRedoFoxes",
        "description" => clienttranslate('Other players may play a Fox to perform the action or pass'),
        "descriptionmyturn" => clienttranslate('${you} may play a Fox to perform the action or pass'),
        "type" => "multipleactiveplayer",
        "possibleactions" => array("playFox", "passFox"),
        "transitions" => array(
            "playFox" => 5,
            "passFox" => 3
        ),
        "action" => "stAllOtherPlayersInit"
    ),

    7 => array(
        "name" => "waitForCranes",
        "description" => clienttranslate('${you} may play a Crane or pass'),
        "type" => "multipleactiveplayer",
        "possibleactions" => array("playCrane", "passCrane"),
        "transitions" => array(
            "playCrane" => 5,
            "passCrane" => 3
        )
    ),

    8 => array(
        "name" => "selectMultipleCardsToCollect",
        "description" => clienttranslate('${actplayer} must choose cards to collect'),
        "descriptionmyturn" => clienttranslate('${you} must choose cards to collect'),
        "type" => "activeplayer",
        "possibleactions" => array("addToCollection"),
        "transitions" => array(
            "addToCollection" => 7,
            "nextPlayer" => 3, // TODO: remove this
        )
    ),

    9 => array(
        "name" => "selectCardToCollect",
        "description" => clienttranslate('${actplayer} must choose a card to collect'),
        "descriptionmyturn" => clienttranslate('${you} must choose a card to collect'),
        "type" => "activeplayer",
        "possibleactions" => array("addToCollection"),
        "transitions" => array(
            "addToCollection" => 7,
            "nextPlayer" => 3, // TODO: remove this
        )
    ),

    10 => array(
        "name" => "kangarooDiscard",
        "description" => clienttranslate('Other players must discard down to 3 cards'),
        "descriptionmyturn" => clienttranslate('${you} must discard down to 3 cards'),
        "type" => "multipleactiveplayer",
        "possibleactions" => array("discardCards"),
        "transitions" => array(
            "nextPlayer" => 3
        ),
        "action" => "stAllNonkangarooPlayersInit"
    ),

    // Final state.
    // Please do not modify (and do not overload action/args methods).
    99 => array(
        "name" => "gameEnd",
        "description" => clienttranslate("End of game"),
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    )

);



