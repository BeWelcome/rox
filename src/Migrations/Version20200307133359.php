<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200307133359 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add the functions needed for message threads';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql("
            CREATE FUNCTION hierarchy_connect_by_parent_eq_prior_id(value INT) RETURNS int(11)
                READS SQL DATA
            BEGIN
                    DECLARE _id INT;
                    DECLARE _parent INT;
                    DECLARE _next INT;
                    DECLARE CONTINUE HANDLER FOR NOT FOUND SET @id = NULL;

                    SET _parent = @id;
                    SET _id = -1;

                    IF @id IS NULL THEN
                            RETURN NULL;
                    END IF;

                    LOOP
                            SELECT  MIN(id)
                            INTO    @id
                            FROM    messages
                            WHERE   Idparent = _parent
                                    AND id > _id;
                            IF @id IS NOT NULL OR _parent = @start_with THEN
                                    SET @level = @level + 1;
                                    RETURN @id;
                            END IF;
                            SET @level := @level - 1;
                            SELECT  id, Idparent
                            INTO    _id, _parent
                            FROM    messages
                            WHERE   id = _parent;
                    END LOOP;
            END;
        "
        );
        $this->addSql("
            CREATE FUNCTION hierarchy_connect_by_parent_eq_prior_id_with_level(value INT, maxlevel INT) RETURNS int(11)
                READS SQL DATA
            BEGIN
                DECLARE _id INT;
                DECLARE _parent INT;
                DECLARE _next INT;
                DECLARE _i INT;
                DECLARE CONTINUE HANDLER FOR NOT FOUND SET @id = NULL;

                SET _parent = @id;
                SET _id = -1;
                SET _i = 0;

                IF @id IS NULL THEN
                        RETURN NULL;
                END IF;

                LOOP
                    SELECT  MIN(id)
                    INTO    @id
                    FROM    messages
                    WHERE   Idparent = _parent
                            AND id > _id
                            AND COALESCE(@level < maxlevel, TRUE);
                    IF @id IS NOT NULL OR _parent = @start_with THEN
                            SET @level = @level + 1;
                            RETURN @id;
                    END IF;
                    SET @level := @level - 1;
                    SELECT  id, Idparent
                    INTO    _id, _parent
                    FROM    messages
                    WHERE   id = _parent;
                    SET _i = _i + 1;
                END LOOP;
                RETURN NULL;
            END;
        ");
        $this->addSql("
            CREATE FUNCTION hierarchy_sys_connect_by_path(delimiter TEXT, node INT) RETURNS text CHARSET latin1
                READS SQL DATA
            BEGIN
                 DECLARE _path TEXT;
                 DECLARE _cpath TEXT;
                 DECLARE _id INT;
                DECLARE EXIT HANDLER FOR NOT FOUND RETURN _path;
                SET _id = COALESCE(node, @id);
                  SET _path = _id;
                LOOP
                            SELECT  idparent
                          INTO    _id
                     FROM    messages
                     WHERE   id = _id
                                AND COALESCE(id <> @start_with, TRUE);
                          SET _path = CONCAT(_id, delimiter, _path);
                 END LOOP;
            END;
        ");
    }

    public function down(Schema $schema) : void
    {
        $this->addSql("
            DROP FUNCTION IF EXISTS hierarchy_connect_by_parent_eq_prior_id;
        ");
        $this->addSql("
            DROP FUNCTION IF EXISTS hierarchy_connect_by_parent_eq_prior_id_with_level
        ");
        $this->addSql("
            DROP FUNCTION IF EXISTS hierarchy_sys_connect_by_path
        ");
    }
}
