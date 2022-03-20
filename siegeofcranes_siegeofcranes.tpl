{OVERALL_GAME_HEADER}

<!--
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- SiegeOfCranes implementation : © <Your name here> <Your email address here>
--
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------

    siegeofcranes_siegeofcranes.tpl

    This is the HTML template of your game.

    Everything you are writing in this file will be displayed in the HTML page of your game user interface,
    in the "main game zone" of the screen.

    You can use in this template:
    _ variables, with the format {MY_VARIABLE_ELEMENT}.
    _ HTML block, with the BEGIN/END format

    See your "view" PHP file to check how to set variables and control blocks

    Please REMOVE this comment before publishing your game on BGA
-->

<div id="playertables">
    <!-- BEGIN player -->
    <div class="playertable whiteblock playertable_{PLAYER_ID}">
        <div class="playertablename" style="color:#{PLAYER_COLOR}">
            {PLAYER_NAME}
        </div>
        <div class="playercollection" id="playercollection_{PLAYER_ID}">
        </div>
    </div>
    <!-- END player -->
</div>

<div id="myhand_wrap" class="whiteblock">
    <h3>My Hand</h3>
    <div id="myhand">
    </div>
</div>


<script type="text/javascript">


var jstpl_cardontable = '<div class="cardontable" id="cardontable_${card_id}" style="background-position:-${x}px -${y}px"></div>';


</script>

{OVERALL_GAME_FOOTER}
