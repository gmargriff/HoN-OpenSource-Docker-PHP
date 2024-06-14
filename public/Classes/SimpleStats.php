<?php

namespace Classes;

class SimpleStats
{

    /// <summary>
    /// The name and clan tag of the account.
    public string $nickname = "";

    /// <summary>
    ///     The ID of the account.
    /// </summary>
    public int $account_id = 1;

    /// <summary>
    ///     The level of the account.
    /// </summary>
    public int $level = 1;

    /// <summary>
    ///     The total experience of the account.
    /// </summary>
    public int $level_experience = 1;

    /// <summary>
    ///     The total number of avatars that the account owns.
    /// </summary>
    public int $avatar_num = 0;

    /// <summary>
    ///     The total number of heroes that the account owns.
    ///     There are currently 139 total heroes.
    /// </summary>
    public int $hero_num = 139;

    /// <summary>
    ///     The total number of matches that the account has played.
    /// </summary>
    // [PhpProperty("total_played")]
    public int $total_played = 0;

    /// <summary>
    ///     The current season.
    ///     The last season before the services went offline was 12.
    /// </summary>
    // [PhpProperty("season_id")]
    public int $season_id = 12;

    /// <summary>
    ///     Unknown.
    /// </summary>
    // [PhpProperty("season_level")]
    public int $season_level = 0;

    /// <summary>
    ///     Simple current season statistics.
    /// </summary>
    // [PhpProperty("season_normal")]
    public $season_normal;

    /// <summary>
    ///     Simple current casual season statistics.
    /// </summary>
    // [PhpProperty("season_casual")]
    public $season_casual;

    /// <summary>
    ///     The total number of MVP awards of the account.
    /// </summary>
    public int $mvp_num = 1004;

    /// <summary>
    ///     The names of the account's top 4 awards.
    /// </summary>
    // [PhpProperty("award_top4_name")]
    public $award_top4_name= [ "awd_masst", "awd_mhdd", "awd_mbdmg", "awd_lgks" ];

    /// <summary>
    ///     The counts of the account's top 4 awards.
    /// </summary>
    // [PhpProperty("award_top4_num")]
    public $award_top4_num= [ 1005, 1006, 1007, 1008 ];

    /// <summary>
    ///     The index of the custom icon equipped, or "0" if no custom icon is equipped.
    /// </summary>
    // [PhpProperty("slot_id")]
    public string $slot_id = "0";

    /// <summary>
    ///     The collection of owned store items.
    ///     <code>
    ///         Chat Name Colour       =>   "cc"
    ///         Chat Symbol            =>   "cs"
    ///         Account Icon           =>   "ai"
    ///         Alternative Avatar     =>   "aa"
    ///         Announcer Voice        =>   "av"
    ///         Taunt                  =>   "t"
    ///         Courier                =>   "c"
    ///         Hero                   =>   "h"
    ///         Early-Access Product   =>   "eap"
    ///         Status                 =>   "s"
    ///         Miscellaneous          =>   "m"
    ///         Ward                   =>   "w"
    ///         Enhancement            =>   "en"
    ///         Coupon                 =>   "cp"
    ///         Mastery                =>   "ma"
    ///         Creep                  =>   "cr"
    ///         Building               =>   "bu"
    ///         Taunt Badge            =>   "tb"
    ///         Teleportation Effect   =>   "te"
    ///         Selection Circle       =>   "sc"
    ///         Bundle                 =>   string.Empty
    ///     </code>
    /// </summary>
    // [PhpProperty("my_upgrades")]
    public $my_upgrades = Array();

    /// <summary>
    ///     The collection of selected store items.
    /// </summary>
    //[PhpProperty("selected_upgrades")]
    public $selected_upgrades= Array();

    /// <summary>
    ///     Metadata attached to each of the account's owned store items.
    /// </summary>
    // [PhpProperty("my_upgrades_info")]
    public $my_upgrades_info= Array();

    /// <summary>
    ///     Unknown.
    /// </summary>
    // [PhpProperty("dice_tokens")]
    public string $dice_tokens = "1";

    /// <summary>
    ///     Unknown.
    /// </summary>
    // [PhpProperty("game_tokens")]
    public int $game_tokens = 0;

    /// <summary>
    ///     Unknown.
    ///     <br/>
    ///     Potentially, the selected level of the upgradable creeps.
    ///     This is also equipable from the owned items vault.
    /// </summary>
    // [PhpProperty("creep_level")]
    public int $creep_level = 0;

    /// <summary>
    ///     The server time (in UTC seconds).
    /// </summary>
    public $timestamp = 0;

    /// <summary>
    ///     Unknown.
    ///     <br/>
    ///     Seems to be set to "5", for some reason.
    /// </summary>
    // [PhpProperty("vested_threshold")]
    public int $vested_threshold = 5;

    /// <summary>
    ///     Unknown.
    ///     <br/>
    ///     Seems to be set to "true" on a successful response, or to "false" if an error occurs.
    /// </summary>
    // [PhpProperty(0)]
    public bool $Zero = true;

    function __construct()
    {   
        $this->season_normal = new SimpleSeasonStats();
        $this->season_casual = new SimpleSeasonStats();
        $this->timestamp = time();
    }
}

class SimpleSeasonStats
{
    /// <summary>
    ///     The number of ranked matches won.
    /// </summary>
    // [PhpProperty("wins")]
    public int $wins = 1001;

    /// <summary>
    ///     The number of ranked matches lost.
    /// </summary>
    // [PhpProperty("losses")]
    public int $losses = 1002;

    /// <summary>
    ///     The current number of consecutive ranked matches won.
    /// </summary>
    //[PhpProperty("win_streak")]
    public int $win_streak = 5;

    /// <summary>
    ///     Whether the account needs to play placement matches or not.
    ///     A value of "1" means TRUE, and a value of "0" means FALSE.
    /// </summary>
    // /[PhpProperty("is_placement")]
    public int $is_placement = 0;

    /// <summary>
    ///     Unknown.
    ///     Potentially, the number of account levels gained during the season.
    /// </summary>
    // [PhpProperty("current_level")]
    public int $current_level = 1;
}
