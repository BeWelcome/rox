<?php

use Rox\Tools\RoxMigration;

class AddMessageHierarchyFunctions extends RoxMigration
{
    public function up()
    {
        // get lineage
        $this->query(<<<SQL_BLOCK

            CREATE FUNCTION `get_lineage`(the_id INT) RETURNS text CHARSET utf8
                READS SQL DATA
            BEGIN
            
             DECLARE v_rec INT DEFAULT 0;
            
             DECLARE done INT DEFAULT FALSE;
             DECLARE v_res text DEFAULT '';
             DECLARE v_papa int;
             DECLARE v_papa_papa int DEFAULT -1;
             DECLARE csr CURSOR FOR
                 select _id,parent_id -- @n:=@n+1 as rownum,T1.* 
              from
              (SELECT @r AS _id,
                    (SELECT @r := table_parent_id FROM messages WHERE table_id = _id) AS parent_id,
                    @l := @l + 1 AS lvl
                FROM
                (SELECT @r := the_id, @l := 0,@n:=0) vars,
                    messages m
                WHERE @r <> 0
                ) T1
                where T1.parent_id is not null
             ORDER BY T1.lvl DESC;
             DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
                open csr;
                read_loop: LOOP
                fetch csr into v_papa,v_papa_papa;
                    SET v_rec = v_rec+1;
                    IF done THEN
                        LEAVE read_loop;
                    END IF;
                    -- add first
                    IF v_rec = 1 THEN
                        SET v_res = v_papa_papa;
                    END IF;
                    SET v_res = CONCAT(v_res,'-',v_papa);
                END LOOP;
                close csr;
                return v_res;
            END;
SQL_BLOCK
        );
        $this->execute("
            CREATE FUNCTION `hierarchy_connect_by_parent_eq_prior_id`(value INT) RETURNS int(11)
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
        ");
        $this->execute("
            CREATE FUNCTION `hierarchy_connect_by_parent_eq_prior_id_with_level`(value INT, maxlevel INT) RETURNS int(11)
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
        $this->execute("
            CREATE DEFINER=`root`@`localhost` FUNCTION `hierarchy_sys_connect_by_path`(delimiter TEXT, node INT) RETURNS text CHARSET latin1
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

    public function down()
    {
        $this->execute("
            DROP function IF EXISTS `hierarchy_sys_connect_by_path`;
            DROP function IF EXISTS `hierarchy_connect_by_parent_eq_prior_id_with_level`;
            DROP function IF EXISTS `hierarchy_connect_by_parent_eq_prior_id`;
            DROP function IF EXISTS `get_lineage`;

        ");
    }
}
