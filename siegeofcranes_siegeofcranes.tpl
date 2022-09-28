{OVERALL_GAME_HEADER}

<!--
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- SiegeOfCranes implementation : © Brock Turner <brocam@gmail.com>
--
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------
-->

<div id="table">
    <div></div>
    <div>{TOP_PLAYER}</div>
    <div></div>

    <div>{LEFT_PLAYER}</div>
    <div class="center">
        <div class="expand"></div>
        <div id="deck_wrap" class="card_wrap">
            <h3>{DECK}: <span id="deckcount"></h3>
            <div id="deck" class="card cardback">
            </div>
        </div>

        <div id="discard_wrap" class="card_wrap">
            <h3>{DISCARD}: <span id="discardcount"></h3>
            <div id="discard" class="card">
            </div>
        </div>
        <div class="expand"></div>
    </div>
    <div>{RIGHT_PLAYER}</div>

    <div></div>
    <div>{THIS_PLAYER}</div>
    <div></div>
</div>

<div id="myhand_wrap" class="whiteblock">
    <h3>{YOUR_HAND}</h3>
    <div id="myhand">
    </div>
</div>


<script type="text/javascript">


var jstpl_cardontable = '<div class="card cardontable" id="cardontable_${card_id}" style="background-position:-${x}px -${y}px"></div>';

var jstpl_cardback = '<div class="card cardback" id="cardback_${id}"></div>';

var jstpl_player_board = '\<div class="cp_board">\
    <div id="stoneicon_p${id}" class="gmk_stoneicon gmk_stoneicon_${color}"></div><span id="stonecount_p${id}">0</span>\
</div>';

</script>

{OVERALL_GAME_FOOTER}
