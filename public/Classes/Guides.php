<?php

namespace Classes;

class Guides
{
    private $guides_file = "";
    private $guides = "";

    function __construct()
    {
        $this->guides_file = file_get_contents(__DIR__ . "/../public_docs/hero_guides.json");
        $this->guides = json_decode($this->guides_file, true);
    }

    private function get_guide_ready_for_list($guide)
    {
        $parameters = ["guideID", "formattedDate", "creator", "name"];
        $votes = number_format(intval($guide["upVotes"]) / (intval($guide["upVotes"]) + intval($guide["downVotes"])), 2, ".", "");
        $static = ["not_def", "not_fav", "$votes", "not_yours", "0"];

        $res = "";
        foreach ($parameters as $p) {
            $res .= $guide[$p] . "|";
        }

        foreach ($static as $s) {
            $res .= $s . "|";
        }

        $res = rtrim($res, "|");

        return $res;
    }

    public function get_guide_list_filtered($hero, $hosttime)
    {
        $split = "`";
        $guides = array_filter($this->guides, function ($entry) use ($hero) {
            return $entry["heroIdentifier"] == $hero;
        });

        $response = array(
            "errors" => "",
            "success" => 1,
            "guide_list" => "",
            "hosttime" => $hosttime,
            "vested_threshold" => 5,
            "0" => 1
        );

        foreach ($guides as $guide) {
            $guide["creator"] = "WhatYouGot";
            $guide["formattedDate"] =  date("d/m/y h:i:sa", strtotime($guide["timestampCreated"]));

            $response["guide_list"] .= $this->get_guide_ready_for_list($guide) . $split;
        }

        $response["guide_list"] = rtrim($response["guide_list"], $split);
        return $response;
    }

    public function get_guide($gid, $hero, $hosttime)
    {
        $guide = array_filter($this->guides, function ($entry) use ($gid) {
            return $entry["guideID"] == $gid;
        });

        if (count($guide) < 1) {
            $guide = array_filter($this->guides, function ($entry) use ($hero) {
                return $entry["heroIdentifier"] == $hero;
            });

            if (count($guide) < 1) {
                $response = [
                    "errors" => "no_guides_found",
                    "success" => 0,
                    "hosttime" => $hosttime,
                    "vested_threshold" => 5,
                    "0" => 1
                ];

                return $response;
            }
        }

        $guide = current($guide);
        $guide["creator"] = "WhatYouGot";
        $guide["formattedDate"] =  date("d/m/y h:i:sa", strtotime($guide["timestampCreated"]));

        $response = [
            "errors" => "",
            "success" => 1,
            "datetime" => $guide["formattedDate"],
            "author_name" => $guide["creator"],
            "hero_cli_name" => $guide["heroIdentifier"],
            "guide_name" => $guide["name"],
            "hero_name" => $guide["heroName"],
            "hero_name" => $guide["heroName"],
            "default" => 0,
            "favorite" => 0,
            "rating" => number_format(intval($guide["upVotes"]) / (intval($guide["upVotes"]) + intval($guide["downVotes"])), 2, ".", ""),
            "thumb" => "noVote",
            "premium" => 0,
            "i_start" => "",
            "i_laning" => "",
            "i_core" => "",
            "i_luxury" => "",
            "abilQ" => "",
            "txt_intro" => $guide["intro"],
            "txt_guide" => $guide["content"],
            "hosttime" => $hosttime,
            "vested_threshold" => 5,
            "0" => 1
        ];

        $field_relation = [
            "startingItems" => "i_start",
            "earlyGameItems" => "i_laning",
            "coreItems" => "i_core",
            "luxuryItems" => "i_luxury",
            "abilityQueue" => "abilQ"
        ];

        foreach ($field_relation as $from => $to) {
            foreach ($guide[$from] as $value) {
                $response[$to] .= $value . "|";
            }
            $response[$to] = rtrim($response[$to], "|");
        }

        return $response;
    }
}
