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
        "description" => clienttranslate('${actplayer} must play a card or draw cards'),
        "descriptionmyturn" => clienttranslate('${you} must play a card as an action, add a card to your collection, or draw 2 cards'),
        "type" => "activeplayer",
        "updateGameProgression" => true,
        "possibleactions" => array("playAction", "playFerret", "playRat", "startCollecting", "addToCollection", "drawCards", "playFinch"),
        "transitions" => array(
            "nextPlayer" => 3,
            "playAction" => 4,
            "waitForRatUndoFoxes" => 20,
            "waitForKangarooUndoFoxes" => 21,
            "waitForFinchUndoFoxes" => 22,
            "waitForFerretUndoFoxes" => 23,
            "addToCollection" => 7,
            "gameEnd" => 99,
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
            "giveCards" => 11,
            "gameEnd" => 99,
        )
    ),

    7 => array(
        "name" => "waitForCranes",
        "description" => clienttranslate('Other players may play a Crane'),
        "descriptionmyturn" => clienttranslate('${you} may play a Crane to discard ${card_count} ${card_name} from ${otherplayer}\'s collection'),
        "args" => "argWaitForCranes",
        "type" => "multipleactiveplayer",
        "possibleactions" => array("playCrane", "passCrane"),
        "transitions" => array(
            "playCrane" => 24,
            "passCrane" => 3
        ),
        "action" => "stAllOtherPlayersInit"
    ),

    8 => array(
        "name" => "selectMultipleCardsToCollect",
        "description" => clienttranslate('${actplayer} must choose card(s) to collect'),
        "descriptionmyturn" => clienttranslate('${you} must choose cards of the same type to collect'),
        "type" => "activeplayer",
        "possibleactions" => array("addToCollection"),
        "transitions" => array(
            "addToCollection" => 7,
        )
    ),

    9 => array(
        "name" => "selectCardToCollect",
        "description" => clienttranslate('${actplayer} must choose card(s) to collect'),
        "descriptionmyturn" => clienttranslate('${you} must choose a card to collect'),
        "type" => "activeplayer",
        "possibleactions" => array("addToCollection"),
        "transitions" => array(
            "addToCollection" => 7,
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

    11 => array(
        "name" => "giveCards",
        "description" => clienttranslate('${otherplayer} must choose 2 cards to give'),
        "descriptionmyturn" => clienttranslate('${you} must choose 2 cards to give'),
        "args" => "argGiveCards",
        "type" => "multipleactiveplayer",
        "possibleactions" => array("giveCards"),
        "transitions" => array(
            "nextPlayer" => 3
        ),
        "action" => "stFinchPlayerInit"
    ),

    // fox undo states

    20 => array(
        "name" => "waitForRatUndoFoxes",
        "description" => clienttranslate('Other players may play a Fox'),
        "descriptionmyturn" => clienttranslate('${you} may play a Fox to prevent ${player1}\'s ${card1_name} and ${player2}\'s ${card2_name} from swapping'),
        "args" => "argWaitForRatFoxes",
        "type" => "multipleactiveplayer",
        "possibleactions" => array("playFox", "passFox"),
        "transitions" => array(
            "playFox" => 25,
            "passFox" => 4
        ),
        "action" => "stAllOtherPlayersInit"
    ),

    21 => array(
        "name" => "waitForKangarooUndoFoxes",
        "description" => clienttranslate('Other players may play a Fox'),
        "descriptionmyturn" => clienttranslate('${you} may play a Fox to prevent all players except ${otherplayer} from discarding down to 3 cards'),
        "args" => "argWaitForKangarooFoxes",
        "type" => "multipleactiveplayer",
        "possibleactions" => array("playFox", "passFox"),
        "transitions" => array(
            "playFox" => 26,
            "passFox" => 4
        ),
        "action" => "stAllOtherPlayersInit"
    ),

    22 => array(
        "name" => "waitForFinchUndoFoxes",
        "description" => clienttranslate('Other players may play a Fox'),
        "descriptionmyturn" => clienttranslate('${you} may play a Fox to prevent ${giver_name} from giving 2 cards to ${receiver_name}'),
        "args" => "argWaitForFinchFoxes",
        "type" => "multipleactiveplayer",
        "possibleactions" => array("playFox", "passFox"),
        "transitions" => array(
            "playFox" => 27,
            "passFox" => 4
        ),
        "action" => "stAllOtherPlayersInit"
    ),

    23 => array(
        "name" => "waitForFerretUndoFoxes",
        "description" => clienttranslate('Other players may play a Fox'),
        "descriptionmyturn" => clienttranslate('${you} may play a Fox to prevent all players from passing their hand to the ${direction_name}'),
        "args" => "argWaitForFerretFoxes",
        "type" => "multipleactiveplayer",
        "possibleactions" => array("playFox", "passFox"),
        "transitions" => array(
            "playFox" => 28,
            "passFox" => 4
        ),
        "action" => "stAllOtherPlayersInit"
    ),

    24 => array(
        "name" => "waitForCraneUndoFoxes",
        "description" => clienttranslate('Other players may play a Fox'),
        "descriptionmyturn" => clienttranslate('${you} may play a Fox to prevent ${otherplayer} from discarding ${card_count} collected ${card_name}'),
        "args" => "argWaitForCranes",
        "type" => "multipleactiveplayer",
        "possibleactions" => array("playFox", "passFox"),
        "transitions" => array(
            "playFox" => 29,
            "passFox" => 4
        ),
        "action" => "stAllOtherPlayersInit"
    ),

    // fox redo states

    25 => array(
        "name" => "waitForRatRedoFoxes",
        "description" => clienttranslate('Other players may play a Fox'),
        "descriptionmyturn" => clienttranslate('${you} may play a Fox to permit ${player1}\'s ${card1_name} and ${player2}\'s ${card2_name} to swap'),
        "args" => "argWaitForRatFoxes",
        "type" => "multipleactiveplayer",
        "possibleactions" => array("playFox", "passFox"),
        "transitions" => array(
            "playFox" => 20,
            "passFox" => 3
        ),
        "action" => "stAllOtherPlayersInit"
    ),

    26 => array(
        "name" => "waitForKangarooRedoFoxes",
        "description" => clienttranslate('Other players may play a Fox'),
        "descriptionmyturn" => clienttranslate('${you} may play a Fox to permit all players except ${otherplayer} to discard down to 3 cards'),
        "args" => "argWaitForKangarooFoxes",
        "type" => "multipleactiveplayer",
        "possibleactions" => array("playFox", "passFox"),
        "transitions" => array(
            "playFox" => 21,
            "passFox" => 3
        ),
        "action" => "stAllOtherPlayersInit"
    ),

    27 => array(
        "name" => "waitForFinchRedoFoxes",
        "description" => clienttranslate('Other players may play a Fox'),
        "descriptionmyturn" => clienttranslate('${you} may play a Fox to permit ${giver_name} to give 2 cards to ${receiver_name}'),
        "args" => "argWaitForFinchFoxes",
        "type" => "multipleactiveplayer",
        "possibleactions" => array("playFox", "passFox"),
        "transitions" => array(
            "playFox" => 22,
            "passFox" => 3
        ),
        "action" => "stAllOtherPlayersInit"
    ),

    28 => array(
        "name" => "waitForFerretRedoFoxes",
        "description" => clienttranslate('Other players may play a Fox'),
        "descriptionmyturn" => clienttranslate('${you} may play a Fox to permit all players to pass their hand to the ${direction_name}'),
        "args" => "argWaitForFerretFoxes",
        "type" => "multipleactiveplayer",
        "possibleactions" => array("playFox", "passFox"),
        "transitions" => array(
            "playFox" => 23,
            "passFox" => 3
        ),
        "action" => "stAllOtherPlayersInit"
    ),

    29 => array(
        "name" => "waitForCraneRedoFoxes",
        "description" => clienttranslate('Other players may play a Fox'),
        "descriptionmyturn" => clienttranslate('${you} may play a Fox to permit ${otherplayer} to discard ${card_count} collected ${card_name}'),
        "args" => "argWaitForCranes",
        "type" => "multipleactiveplayer",
        "possibleactions" => array("playFox", "passFox"),
        "transitions" => array(
            "playFox" => 24,
            "passFox" => 3
        ),
        "action" => "stAllOtherPlayersInit"
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



