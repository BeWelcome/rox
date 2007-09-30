DROP TABLE IF EXISTS membersalias;
CREATE TABLE membersalias ( 
    id         	int(11) NOT NULL auto_increment,
    IdMember   	int(11) NOT NULL,
    networkId  	smallint(6) NOT NULL COMMENT 'The external network (CS/HC) for which the alias is valid',
    alias      	varchar(50) NOT NULL COMMENT 'The primary identifier for the external network. This should be a username.',
    alternative	varchar(50) NULL COMMENT 'A secundary identifier, for example a numeric id. (neccesary for CS)',
	verified boolean DEFAULT false COMMENT 'Has this alias been verified in any way? (For future use)',
	primary key(id),
	unique(networkId,alias)
    )
;
DROP TABLE IF EXISTS externalcomments;
CREATE TABLE externalcomments ( 
    id               	int(11) AUTO_INCREMENT NOT NULL,
    idMember         	int(11) NOT NULL,
    networkId        	int(6) NOT NULL,
    targetAlias      	varchar(50) NOT NULL,
    targetAlternative	varchar(50) NULL,
    comment          	text NOT NULL,
    quality          	enum('Good','Neutral','Bad') NOT NULL DEFAULT 'Neutral',
    PRIMARY KEY(id)
)
;
