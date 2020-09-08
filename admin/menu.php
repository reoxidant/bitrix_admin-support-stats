<?
IncludeModuleLangFile(__FILE__);

if (!$USER -> IsAuthorized())
    return false;

$aMenu = array(
    "parent_menu" => "global_menu_services",
    "section" => "support",
    "sort" => 300,
    "text" => GetMessage("SUP_M_SUPPORT"),
    "title" => GetMessage("SUP_M_SUPPORT_TITLE"),
    "icon" => "support_menu_icon",
    "page_icon" => "support_page_icon",
    "items_id" => "menu_support",
    //"url" => "ticket_index.php?lang=".LANGUAGE_ID,
    "items" => array()
);

$aMenu["items"][] = array(
    "text" => GetMessage("SUP_M_STATS"),
    "title" => GetMessage("SUP_M_STATS_TITLE"),
    "items_id" => "menu_support_stats",
    "page_icon" => "support_page_icon",
    "items" => array(
        array(
            "text" => GetMessage("SUP_M_TICKETS_STATS"),
            "url" => "ticket_stats_graph.php?lang=" . LANGUAGE_ID,
            "more_url" => array(
                "ticket_stats_list.php",
            ),
            "title" => GetMessage("SUP_M_TICKETS_STATS")
        ),
        array(
            "text" => GetMessage("SUP_M_TICKETS_COUNT_STATS"),
            "url" => "ticket_stats_list.php?lang=" . LANGUAGE_ID,
            "more_url" => array(
                "ticket_stats_graph.php",
            ),
            "title" => GetMessage("SUP_M_TICKETS_COUNT_STATS")
        ),
        array(
            "text" => GetMessage("SUP_M_INSTRUCTIONS_STATS"),
            "url" => "ticket_stats_instructions.php?lang=" . LANGUAGE_ID,
            "more_url" => array(
                "ticket_stats_instructions.php",
            ),
            "title" => GetMessage("SUP_M_INSTRUCTIONS_STATS")
        )
    )
);

return $aMenu;

?>
