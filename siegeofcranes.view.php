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
 * siegeofcranes.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in siegeofcranes_siegeofcranes.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 *
 */

require_once( APP_BASE_PATH."view/common/game.view.php" );

class view_siegeofcranes_siegeofcranes extends game_view
{
    function getGameName() {
        return "siegeofcranes";
    }

    function build_page($viewArgs) {
        global $g_user;
        $current_player_id = $g_user->get_id();

        // Get players & players number
        $players = $this->game->loadPlayersBasicInfos();
        $players_nbr = count($players);

        /*********** Place your code below:  ************/

        $template = self::getGameName() . "_" . self::getGameName();

        $player_ids = array_keys($players);
        $index = array_search($current_player_id, $player_ids);
        $first_half = array_slice($player_ids, 0, $index);
        $last_half = array_slice($player_ids, $index);
        $reorderd_ids = array_merge($last_half, $first_half);

        // default: 4 players
        $positions = array("THIS_PLAYER", "LEFT_PLAYER", "TOP_PLAYER", "RIGHT_PLAYER");
        if ($players_nbr == 3) {
            $positions = array("THIS_PLAYER", "TOP_PLAYER", "RIGHT_PLAYER");
            $this->tpl["LEFT_PLAYER"] = self::_("");
        } else if ($players_nbr == 2) {
            $positions = array("THIS_PLAYER", "TOP_PLAYER");
            $this->tpl["LEFT_PLAYER"] = self::_("");
            $this->tpl["RIGHT_PLAYER"] = self::_("");
        }

        foreach ($reorderd_ids as $key => $player_id) {
            $player_name = $players[$player_id]['player_name'];
            if ($player_id == $current_player_id) {
                $player_name = self::_("You");
            }

            $player_color = $players[$player_id]['player_color'];
            $this->tpl[$positions[$key]] = self::_(
                "<div class=\"playertable whiteblock playertable_$player_id\">
                    <div class=\"playertablename\" style=\"color:#$player_color\">$player_name</div>
                    <div>Number of cards in hand: <span id=\"handcount_$player_id\"></span></div>
                    <div class=\"playercollection\" id=\"playercollection_$player_id\"></div>
                </div>"
            );
        }

        $this->tpl['YOUR_HAND'] = self::_("Your Hand");
        $this->tpl['DECK'] = self::_("Deck");
        $this->tpl['DISCARD'] = self::_("Discard");


        /*********** Do not change anything below this line  ************/
    }
}


