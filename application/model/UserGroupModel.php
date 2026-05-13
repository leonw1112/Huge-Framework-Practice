<?php

class UserGroupModel
{
    /**
     * Alle Benutzergruppen aus der Datenbank holen
     */
    public static function getAllGroups()
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT group_id, group_name, group_description 
                FROM user_groups 
                ORDER BY group_id ASC";
        $query = $database->prepare($sql);
        $query->execute();

        return $query->fetchAll();
    }

    /**
     * Einzelne Gruppe per ID holen
     */
    public static function getGroupById($group_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT group_id, group_name, group_description 
                FROM user_groups 
                WHERE group_id = :group_id 
                LIMIT 1";
        $query = $database->prepare($sql);
        $query->execute(array(':group_id' => $group_id));

        return $query->fetch();
    }
}