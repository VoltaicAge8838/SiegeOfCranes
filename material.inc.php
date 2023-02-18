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
 * material.inc.php
 *
 * SiegeOfCranes game material description
 *
 * Here, you can describe the material of your game with PHP variables.
 *
 * This file is loaded in your game logic class constructor, ie these variables
 * are available everywhere in your game logic code.
 *
 */

$this->invalid_card_selection_message = self::_("Invalid card(s) selected");
$this->invalid_card_type_message = self::_("Invalid card type played");

$this->direction_names = array(
    0 => clienttranslate('left'),
    1 => clienttranslate('right'),
);

$this->card_types = array(
    1 => array(
        "name" => clienttranslate('Mischief of Rats'),
        "shortName" => clienttranslate('Rat(s)'),
        "attack" => 1,
        "react" => 0,
        "description" => clienttranslate('Pick any 2 players. Take 1 collection card from each of them and place it in the other player\'s collection.')
    ),
    2 => array(
        "name" => clienttranslate('Embarassment of Pandas'),
        "shortName" => clienttranslate('Panda(s)'),
        "attack" => 0,
        "react" => 0,
        "description" => clienttranslate('Discard your hand, then draw 5 cards.')
    ),
    3 => array(
        "name" => clienttranslate('Court of Kangaroos'),
        "shortName" => clienttranslate('Kangaroo(s)'),
        "attack" => 1,
        "react" => 0,
        "description" => clienttranslate('Draw 2 cards. All other players discard down to 3 cards.')
    ),
    4 => array(
        "name" => clienttranslate('Skulk of Foxes'),
        "shortName" => clienttranslate('Fox(es)'),
        "attack" => 1,
        "react" => 1,
        "description" => clienttranslate('Play this when an attack card is being played to cancel that attack card.')
    ),
    5 => array(
        "name" => clienttranslate('Charm of Finches'),
        "shortName" => clienttranslate('Finch(es)'),
        "attack" => 1,
        "react" => 0,
        "description" => clienttranslate('Pick a player with at least 3 cards. They give you 2 cards, their choice.')
    ),
    6 => array(
        "name" => clienttranslate('Business of Ferrets'),
        "shortName" => clienttranslate('Ferret(s)'),
        "attack" => 1,
        "react" => 0,
        "description" => clienttranslate('All players pass their hand to the player on their right or left, your choice.')
    ),
    7 => array(
        "name" => clienttranslate('Bask of Crocodiles'),
        "shortName" => clienttranslate('Crocodile(s)'),
        "attack" => 0,
        "react" => 0,
        "description" => clienttranslate('Draw 2 cards, then add 1 card to your collection.')
    ),
    8 => array(
        "name" => clienttranslate('Siege of Cranes'),
        "shortName" => clienttranslate('Crane(s)'),
        "attack" => 1,
        "react" => 1,
        "description" => clienttranslate('Play this when cards are being added to a collection to discard them instead.')
    ),
    9 => array(
        "name" => clienttranslate('Band of Coyotes'),
        "shortName" => clienttranslate('Coyote(s)'),
        "attack" => 0,
        "react" => 0,
        "description" => clienttranslate('Add multiple cards of the same type to a collection.')
    ),
    10 => array(
        "name" => clienttranslate('Party of Jays'),
        "shortName" => clienttranslate('Jay(s)'),
        "attack" => 0,
        "react" => 0,
        "description" => clienttranslate('Draw 4 cards. All other players draw 1 card.')
    )
);
