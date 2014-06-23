<?php
	class GoogleLoginHooks {
		public static function onUserLogoutComplete() {
			global $wgRequest;
			if ( $wgRequest->getSessionData( 'access_token' ) !== null ) {
				$wgRequest->setSessionData( 'access_token', '' );
			}
		}

		public static function onLoadExtensionSchemaUpdates( $updater = null ) {
			global $wgSharedDB, $wgDBname, $wgDBtype;
			// Don't create tables on a shared database
			if( !empty( $wgSharedDB ) && $wgSharedDB !== $wgDBname ) {
				return true;
			}
			// Tables to add to the database
			$tables = array( 'user_google_user' );
			// Sql directory inside the extension folder
			$sql = dirname( __FILE__ ) . '/sql';
			// Extension of the table schema file (depending on the database type)
			switch ( $updater !== null ? $updater->getDB()->getType() : $wgDBtype ) {
				default:
					$ext = 'sql';
			}
			// Do the updating
			foreach ( $tables as $table ) {
				// Location of the table schema file
				$schema = "$sql/$table.$ext";
				$updater->addExtensionUpdate( array( 'addTable', $table, $schema, true ) );
			}
			return true;
		}

		public static function onUserLoginForm( &$template ) {
			global $wgOut;
			$wgOut->addHtml(
				Html::openelement( 'div', array( 'style' => 'float:right;' ), null)
			);
			$wgOut->addHtml( Html::openelement( 'li', array(
					'style' => 'list-style:none;',
				) ) .
				Html::element( 'a' , array(
						'href' => Title::makeTitle( -1, 'GoogleLogin' )->getLocalUrl(),
						'class' => 'mw-ui-button mw-ui-destructive mw-ui-big',
					), wfMessage( 'googlelogin' )
				) .
				Html::closeelement( 'li' )
			);
			$wgOut->addHtml(
				Html::closeelement( 'div')
			);
		}
	}