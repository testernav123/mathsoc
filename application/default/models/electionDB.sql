-- Todo:  Include functionality to nominate candidates in the next version

CREATE TABLE elections (
  electionId	  INT(8)	NOT NULL	AUTO_INCREMENT,
  position	  VARCHAR(255)	NOT NULL,
  description	  TEXT,
  CRO		  CHAR(8)	NOT NULL,
  CRO_ballot	  VARCHAR(128),
  nomination_needed INT(8),
  nomination_open DATETIME,
  nomination_close DATETIME,
  voting_open	  DATETIME,
  voting_close	  DATETIME,

  PRIMARY KEY (electionId)
);

CREATE TABLE nominations (
  electionId      INT(8)	NOT NULL,
  candidateId     CHAR(8)	NOT NULL,
  nominatorId     CHAR(8)	NOT NULL,

  PRIMARY KEY (electionId, candidateId, nominatorId)
);

CREATE TABLE candidates (
  electionId	  INT(8)	NOT NULL,
  candidateId	  CHAR(8)	NOT NULL,
  link		  VARCHAR(255),

  PRIMARY KEY (electionId, candidateId)
);

CREATE TABLE voters (
  electionId	  INT(8)	NOT NULL,
  userId	  CHAR(8)	NOT NULL,
  voted		  BOOL		DEFAULT 0,

  PRIMARY KEY (electionId, userId)
);

CREATE TABLE votes (
  electionId	  INT(8)	NOT NULL,
  vote		  VARCHAR(128)	NOT NULL
);
