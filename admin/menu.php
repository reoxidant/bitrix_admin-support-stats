<?
IncludeModuleLangFile(__FILE__);
if (!$USER -> IsAuthorized())
    return false;
$SUP_RIGHT = $APPLICATION -> GetGroupRight("support");
if ($SUP_RIGHT > "D") {
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

    if ($SUP_RIGHT >= "T")
        $aMenu["items"][] = array(
            "text" => GetMessage("SUP_M_REPORT_TABLE"),
            "url" => "ticket_desktop.php?lang=" . LANGUAGE_ID . "&amp;set_default=Y",
            "more_url" => array("ticket_desktop.php"),
            "title" => GetMessage("SUP_M_REPORT_TABLE_ALT")
        );

    if ($SUP_RIGHT >= "T")
        $aMenu["items"][] = array(
            "text" => GetMessage("SUP_M_TICKETS"),
            "url" => "ticket_list.php?lang=" . LANGUAGE_ID . "&amp;set_default=Y",
            "more_url" => array(
                "ticket_list.php",
                "ticket_edit.php",
                "ticket_message_edit.php"
            ),
            "title" => GetMessage("SUP_M_TICKETS_ALT")
        );
    $aMenu["items"][] = array(
        "text" => GetMessage("SUP_M_TICKETS"),
        "url" => "ticket_list.php?lang=" . LANGUAGE_ID . "&amp;set_default=Y",
        "more_url" => array(
            "ticket_list.php",
            "ticket_edit.php",
            "ticket_message_edit.php"
        ),
        "title" => GetMessage("SUP_M_TICKETS_ALT")
    );

    if ($SUP_RIGHT >= "T")
        $aMenu["items"][] = array(
            "text" => GetMessage("SUP_M_REPORT_GRAPH"),
            "url" => "ticket_report_graph.php?lang=" . LANGUAGE_ID . "&amp;set_default=Y",
            "more_url" => array("ticket_report_graph.php"),
            "title" => GetMessage("SUP_M_REPORT_GRAPH_ALT")
        );

    if ($SUP_RIGHT >= "V") {
        $aMenu["items"][] = array(
            "text" => GetMessage("SUP_M_DICT"),
            "title" => GetMessage("SUP_M_DICT_TITLE"),
            //"url" => "ticket_dict_index.php?lang=".LANGUAGE_ID,
            "items_id" => "menu_support_dict",
            "page_icon" => "support_page_icon",
            "items" => array(
                array(
                    "text" => GetMessage("SUP_M_CATEGORY"),
                    "url" => "ticket_dict_list.php?lang=" . LANGUAGE_ID . "&amp;find_type=C",
                    "more_url" => array(
                        "ticket_dict_edit.php?find_type=C",
                        "ticket_dict_list.php?find_type=C"
                    ),
                    "title" => GetMessage("SUP_M_CATEGORY")
                ),
                array(
                    "text" => GetMessage("SUP_M_CRITICALITY"),
                    "url" => "ticket_dict_list.php?lang=" . LANGUAGE_ID . "&amp;find_type=K",
                    "more_url" => array(
                        "ticket_dict_edit.php?find_type=K",
                        "ticket_dict_list.php?find_type=K"
                    ),
                    "title" => GetMessage("SUP_M_CRITICALITY")
                ),
                array(
                    "text" => GetMessage("SUP_M_STATUS"),
                    "url" => "ticket_dict_list.php?lang=" . LANGUAGE_ID . "&amp;find_type=S",
                    "more_url" => array(
                        "ticket_dict_edit.php?find_type=S",
                        "ticket_dict_list.php?find_type=S"
                    ),
                    "title" => GetMessage("SUP_M_STATUS")
                ),
                array(
                    "text" => GetMessage("SUP_M_MARK"),
                    "url" => "ticket_dict_list.php?lang=" . LANGUAGE_ID . "&amp;find_type=M",
                    "more_url" => array(
                        "ticket_dict_edit.php?find_type=M",
                        "ticket_dict_list.php?find_type=M"
                    ),
                    "title" => GetMessage("SUP_M_MARK")
                ),
                array(
                    "text" => GetMessage("SUP_M_FUA"),
                    "url" => "ticket_dict_list.php?lang=" . LANGUAGE_ID . "&amp;find_type=F",
                    "more_url" => array(
                        "ticket_dict_edit.php?find_type=F",
                        "ticket_dict_list.php?find_type=F"
                    ),
                    "title" => GetMessage("SUP_M_FUA")
                ),
                array(
                    "text" => GetMessage("SUP_M_SOURCE"),
                    "url" => "ticket_dict_list.php?lang=" . LANGUAGE_ID . "&amp;find_type=SR",
                    "more_url" => array(
                        "ticket_dict_edit.php?find_type=SR",
                        "ticket_dict_list.php?find_type=SR"
                    ),
                    "title" => GetMessage("SUP_M_SOURCE")
                ),

                array(
                    "text" => GetMessage("SUP_M_DIFFICULTY"),
                    "url" => "ticket_dict_list.php?lang=" . LANGUAGE_ID . "&amp;find_type=D",
                    "more_url" => array(
                        "ticket_dict_edit.php?find_type=D",
                        "ticket_dict_list.php?find_type=D"
                    ),
                    "title" => GetMessage("SUP_M_DIFFICULTY_TITLE")
                ),
            ),
        );
        $aMenu["items"][] = array(
            "text" => GetMessage("SUP_M_SLA"),
            "url" => "ticket_sla_list.php?lang=" . LANGUAGE_ID,
            "more_url" => array(
                "ticket_sla_list.php",
                "ticket_sla_edit.php"
            ),
            "title" => GetMessage("SUP_M_SLA")
        );
        $aMenu["items"][] = array(
            "text" => GetMessage("SUP_M_SHEDULE"),
            "title" => GetMessage("SUP_M_SHEDULE_TITLE"),
            //"url" => "ticket_shedule_index.php?lang=".LANGUAGE_ID,
            "items_id" => "menu_support_shed",
            "page_icon" => "support_page_icon",
            "items" => array(
                array(
                    "text" => GetMessage("SUP_M_TIMETABLE"),
                    "url" => "ticket_timetable_list.php?lang=" . LANGUAGE_ID,
                    "more_url" => array(
                        "ticket_timetable_list.php",
                        "ticket_timetable_edit.php"
                    ),
                    "title" => GetMessage("SUP_M_TIMETABLE")
                ),
                array(
                    "text" => GetMessage("SUP_M_HOLIDAYS"),
                    "url" => "ticket_holidays_list.php?lang=" . LANGUAGE_ID,
                    "more_url" => array(
                        "ticket_holidays_list.php",
                        "ticket_holidays_edit.php"
                    ),
                    "title" => GetMessage("SUP_M_HOLIDAYS")
                )
            )
        );
        $aMenu["items"][] = array(
            "text" => GetMessage("SUP_M_GROUPS"),
            "url" => "ticket_group_list.php?lang=" . LANGUAGE_ID,
            "more_url" => array(
                "ticket_group_list.php",
                "ticket_group_edit.php",
            ),
            "title" => GetMessage("SUP_M_GROUPS_TITLE")
        );
        $aMenu["items"][] = array(
            "text" => GetMessage("SUP_M_COUPONS"),
            "url" => "ticket_coupon_list.php?lang=" . LANGUAGE_ID,
            "more_url" => array(
                "ticket_coupon_edit.php",
                "ticket_coupon_list.php",
            ),
            "title" => GetMessage("SUP_M_COUPONS_TITLE")
        );
        $aMenu["items"][] = array(
            "text" => GetMessage("SUP_M_COUPONS_LOG"),
            "url" => "ticket_coupon_log.php?lang=" . LANGUAGE_ID,
            "more_url" => array(
                "ticket_coupon_log.php",
            ),
            "title" => GetMessage("SUP_M_COUPONS_LOG_TITLE")
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
    }
    return $aMenu;
}
return false;
?>
