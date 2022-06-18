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

<div id="table">
    <div></div>
    <div>
        <!-- BEGIN topplayer -->
        <div class="playertable whiteblock playertable_{PLAYER_ID}">
            <div class="playertablename" style="color:#{PLAYER_COLOR}">
                {PLAYER_NAME}
            </div>
            <div class="playercollection" id="playercollection_{PLAYER_ID}">
            </div>
        </div>
        <!-- END topplayer -->
    </div>
    <div></div>

    <div>
        <!-- BEGIN leftplayer -->
        <div class="playertable whiteblock playertable_{PLAYER_ID}">
            <div class="playertablename" style="color:#{PLAYER_COLOR}">
                {PLAYER_NAME}
            </div>
            <div class="playercollection" id="playercollection_{PLAYER_ID}">
            </div>
        </div>
        <!-- END leftplayer -->
    </div>
    <div class="center">
        <div class="expand"></div>
        <div id="deck_wrap" class="whiteblock card_wrap">
            <h3>{DECK}</h3>
            <div id="deck">
            </div>
        </div>

        <div id="discard_wrap" class="whiteblock card_wrap">
            <h3>{DISCARD}</h3>
            <div id="discard">
            </div>
        </div>
        <div class="expand"></div>
    </div>
    <div>
        <!-- BEGIN rightplayer -->
        <div class="playertable whiteblock playertable_{PLAYER_ID}">
            <div class="playertablename" style="color:#{PLAYER_COLOR}">
                {PLAYER_NAME}
            </div>
            <div class="playercollection" id="playercollection_{PLAYER_ID}">
            </div>
        </div>
        <!-- END rightplayer -->
    </div>

    <div></div>
    <div>
        <!-- BEGIN thisplayer -->
        <div class="playertable whiteblock playertable_{PLAYER_ID}">
            <div class="playertablename" style="color:#{PLAYER_COLOR}">
                {PLAYER_NAME}
            </div>
            <div class="playercollection" id="playercollection_{PLAYER_ID}">
            </div>
        </div>
        <!-- END thisplayer -->
    </div>
    <div></div>
</div>

<div id="myhand_wrap" class="whiteblock">
    <h3>{YOUR_HAND}</h3>
    <div id="myhand">
    </div>
</div>


<script type="text/javascript">


var jstpl_cardontable = '<div class="cardontable" id="cardontable_${card_id}" style="background-position:-${x}px -${y}px"></div>';

var jstpl_player_board = '\<div class="cp_board">\
    <div id="stoneicon_p${id}" class="gmk_stoneicon gmk_stoneicon_${color}"></div><span id="stonecount_p${id}">0</span>\
</div>';

</script>

{OVERALL_GAME_FOOTER}
