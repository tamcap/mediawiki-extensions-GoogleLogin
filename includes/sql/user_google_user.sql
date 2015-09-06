--
-- extension Google Login SQL schema
--
CREATE TABLE /*$wgDBprefix*/user_google_user (
  user_googleid DECIMAL(25,0) unsigned NOT NULL PRIMARY KEY,
  user_id int(10) unsigned NOT NULL
) /*$wgDBTableOptions*/;

CREATE TABLE /*$wgDBprefix*/user_google_explicitly_allowed (
  `user_email` varbinary(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`user_email`)
) /*$wgDBTableOptions*/;
