<?php

class ChatweeV2_FormAttributes
{

    public static function moderatorRoles()
    {
        return Array ( "administrator", "editor", "author", "contributor", "subscriber" );
    }

    public static function displayList()
    {
        return Array ( "main_page", "search_page", "archive_page", "post_page", "single_page" );
    }
}