<?php

const COLORS = [
    "rgba(255,225,0,1)", "rgba(61,89,171,1)", "rgba(143,64,153,1)", "rgba(250,103,0,1)",
    "rgba(136,210,255,1)", "rgba(217,0,0,1)", "rgba(0,160,82,1)", "rgba(255,158,219,1)", "rgba(0,101,184,1)",
    "rgba(255,144,114,1)","rgba(81,53,150,1)", "rgba(254,151,0,1)", "rgba(169,48,116,1)", "rgba(248,233,31,1)",
    "rgba(138,32,19,1)","rgba(163,218,101,1)", "rgba(101,64,11,1)", "rgba(224,66,0,1)", "rgba(1,59,0,1)"
];

const RANKS = [
    "Silver I", "Silver II", "Silver III", "Silver IV", "Silver Elite", "Silver Elite Master",
    "Gold Nova I", "Gold Nova II", "Gold Nova III", "Gold Nova Master", "Master Guardian I", "Master Guardian II",
    "Master Guardian Elite", "Distinguished Master Guardian", "Legendary Eagle", "Legendary Eagle Master",
    "Supreme Master First Class", "Global Elite"
];

const SHORT_RANKS = [
    "S1", "S2", "S3", "S4", "SE", "SEM", "GN1", "GN2", "GN3",
    "GNM", "MG1", "MG2", "MGE", "DMG", "LE", "LEM","SMFC", "GE"
];

function getDBConnection() {
    $db = new PDO('mysql:host=127.0.0.1;port=3306;dbname=csgosquad', 'csgosquad', '', array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION));
    return $db;
}