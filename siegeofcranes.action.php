<?php
/**
 *------
* BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
* SiegeOfCranes implementation : © <Your name here> <Your email address here>
*
* This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
* See http://en.doc.boardgamearena.com/Studio for more information.
* -----
*
* siegeofcranes.action.php
*
* SiegeOfCranes main action entry point
*
*
* In this file, you are describing all the methods that can be called from your
* user interface logic (javascript).
*
* If you define a method "myAction" here, then you can call it from your javascript code with:
* this.ajaxcall( "/siegeofcranes/siegeofcranes/myAction.html", ...)
*
*/

class action_siegeofcranes extends APP_GameAction
{
    // Constructor: please do not modify
    public function __default()
    {
        if( self::isArg( 'notifwindow') )
        {
            $this->view = "common_notifwindow";
            $this->viewArgs['table'] = self::getArg( "table", AT_posint, true );
        }
        else
        {
            $this->view = "siegeofcranes_siegeofcranes";
            self::trace( "Complete reinitialization of board game" );
        }
    }

    // TODO: defines your action entry points there

    public function playAction() {
        self::setAjaxMode();
        $card_id = self::getArg("id", AT_posint, true);
        $this->game->playAction($card_id);
        self::ajaxResponse();
    }

    public function playFerret() {
        self::setAjaxMode();
        $card_id = self::getArg("id", AT_posint, true);
        $direction = self::getArg("direction", AT_posint, true);
        $this->game->playFerret($card_id, $direction);
        self::ajaxResponse();
    }

    public function playRat() {
        self::setAjaxMode();
        $card_id = self::getArg("id", AT_posint, true);
        $target1_id = self::getArg("target1_id", AT_posint, true);
        $target2_id = self::getArg("target2_id", AT_posint, true);
        $this->game->playRat($card_id, $target1_id, $target2_id);
        self::ajaxResponse();
    }

    public function addToCollection() {
        self::setAjaxMode();
        $raw_card_ids = self::getArg("ids", AT_numberlist, true);
        $card_ids = explode( ',', $raw_card_ids );
        $this->game->addToCollection($card_ids);
        self::ajaxResponse();
    }

    public function drawCards() {
        self::setAjaxMode();
        $this->game->drawCardsAction();
        self::ajaxResponse();
    }

    public function playFox() {
        self::setAjaxMode();
        $card_id = self::getArg("id", AT_posint, true);
        $this->game->playFox($card_id);
        self::ajaxResponse();
    }

    public function passFox() {
        self::setAjaxMode();
        $this->game->passFox();
        self::ajaxResponse();
    }


    /*

    Example:

    public function myAction()
    {
        self::setAjaxMode();

        // Retrieve arguments
        // Note: these arguments correspond to what has been sent through the javascript "ajaxcall" method
        $arg1 = self::getArg( "myArgument1", AT_posint, true );
        $arg2 = self::getArg( "myArgument2", AT_posint, true );

        // Then, call the appropriate method in your game logic, like "playCard" or "myAction"
        $this->game->myAction( $arg1, $arg2 );

        self::ajaxResponse( );
    }

    */

}
