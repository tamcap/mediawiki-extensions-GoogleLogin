<?php
	class SpecialManageExplicitlyAllowed extends SpecialPage {
		/** @var $mGoogleLogin saves an instance of GoogleLogin class */
		private $mGoogleLogin;

		/** @var string $manageableEmail Email address to manage */
		private static $manageableEmail = null;

		function __construct() {
			parent::__construct( 'ManageExplicitlyAllowed', 'manageexplicitlyallowed' );
			$this->listed = true;
		}

		/**
		 * Special page executer
		 * @param SubPage $par Subpage
		 */
		function execute( $par ) {
			$user = $this->getUser();
			$out = $this->getOutput();
			$request = $this->getRequest();
			if ( !$this->userCanExecute( $user ) ) {
				$this->displayRestrictionError();
				return;
			}
			$this->setHeaders();
			if ( !$request->getVal( 'glManageableEmail' ) ) {
				// $out->addModules( 'mediawiki.userSuggest' );
				$db = new GoogleLoginDB;
				$explicitlyAllowedEmails = $db->getExplicitlyAllowedEmails();
				if (!empty($explicitlyAllowedEmails)) {
					$list = "";
					foreach ($explicitlyAllowedEmails as $email) {
						$list .= Html::openElement('li') . $email . Html::closeElement( 'li' );
					}
					$out->addWikiMsg('googlelogin-manage-currentemaillist');
					$out->addHtml(
						Html::openElement( 'ul' ) .
						$list .
						Html::closeElement( 'ul' )
					);
				}

				$formFields = array(
					'email' => array(
						'type' => 'text',
						'name' => 'email',
						'label-message' => 'googlelogin-email',
						'id' => 'mw-gl-email',
						// 'cssclass' => 'mw-autocomplete-user',
						'autofocus' => true,
					)
				);
				$htmlForm = new HTMLForm( $formFields, $this->getContext(), 'googlelogin-manage' );
				$htmlForm->setWrapperLegendMsg( $this->msg( 'googlelogin-manageemaillegend' ) );
				$htmlForm->setSubmitText( $this->msg( 'googlelogin-manage-emailsubmit' )->text() );
				$htmlForm->setSubmitProgressive();
				$htmlForm->setSubmitCallback( array( $this, 'submitEmail' ) );
				$htmlForm->show();
			} else {
				$this->submitEmail(
					array(
						'email' => $request->getVal( 'glManageableEmail' )
					)
				);
			}

			if ( self::$manageableEmail ) {
				$this->manageEmail( self::$manageableEmail );
			} else {

			}
		}

		/**
		 * Dummy check by now
		 *
		 * @param array $data Formdata
		 * @return boolean
		 */
		public function submitEmail( $data ) {
			if ( !isset( $data['email'] ) ) {
				return false;
			}

			self::$manageableEmail = $data['email'];
			return true;
		}

		/**
		 * Renders a form to manage this email and handles all actions.
		 *
		 * @param string $email
		 */
		private function manageEmail( $email ) {
			$request = $this->getRequest();
			$out = $this->getOutput();

			$out->addModules(
				array(
					'ext.GoogleLogin.specialManage.scripts',
					'ext.GoogleLogin.style',
				)
			);
			$out->addBackLinkSubtitle( $this->getPageTitle() );
			$db = new GoogleLoginDB;
			$isAllowed = $db->isExplicitlyAllowed($email);
			// $emailA = $request->getVal( 'explicit-email' );
			// var_dump($emailA);die();
			$allowEmail = $request->getVal( 'allow-email' );
			$disallowEmail = $request->getVal( 'disallow-email' );
			if (isset($disallowEmail) && $email) {
				// disallow the email
				if ( $db->disallowEmail($email) ) {
					$out->addWikiMsg('googlelogin-manage-disallowsuccess');
					$isAllowed = false;
				} else {
					$out->addWikiMsg( 'googlelogin-manage-changederror' );
				}
			} elseif ( isset($allowEmail) && $email ) {
				// allow the email
				if ( $db->allowEmail($email) ) {
					$out->addWikiMsg('googlelogin-manage-allowsuccess');
					$isAllowed = true;
				} else {
					$out->addWikiMsg( 'googlelogin-manage-changederror' );
				}
			}
			$out->addWikiMsg( 'googlelogin-manage-email', $email );
			if ( $isAllowed ) {
				$out->addHtml(
					Html::openElement( 'div' ) .
					$this->msg( 'googlelogin-manage-allowed' )->escaped() .
					Html::closeElement( 'div' )
				);

			} else {
				$out->addHtml(
					Html::openElement( 'div' ) .
					$this->msg( 'googlelogin-manage-notallowed' )->escaped() .
					Html::closeElement( 'div' )
				);
			}
			// $formEmail = $email;
			//
			// $formFields = array(
			// 	'explicit-email' => array(
			// 		'type' => 'text',
			// 		'name' => 'explicit-email',
			// 		'label-raw' => 'Email:',
			// 		'default' => $formEmail,
			// 		'id' => 'mw-gl-email',
			// 	)
			// );
			$formFields = [];
			$htmlForm = new HTMLForm( $formFields, $this->getContext(), 'googlelogin-change' );
			$htmlForm->addHiddenField( 'glManageableEmail', $email );
			$htmlForm->setWrapperLegendMsg( $this->msg( 'googlelogin-manage-changelegendEmail' ) );

			$htmlForm->setSubmitCallback( array( 'SpecialManageExplicitlyAllowed', 'submitEmailA' ) );
			if ( $isAllowed ) {
				$htmlForm->setSubmitName('disallow-email');
				$htmlForm->setSubmitText($this->msg( 'googlelogin-manage-disallowbutton' )->text());
			} else {
				$htmlForm->setSubmitName('allow-email');
				$htmlForm->setSubmitText($this->msg( 'googlelogin-manage-allowbutton' )->text());
			}
			$htmlForm->show();
		}

		/**
		 * Submithandler for new google id
		 *
		 * @param array $data Formdata
		 * @return boolean
		 */
		public static function submitEmailA( $data ) {
			return false;
		}

		protected function getGroupName() {
			return 'users';
		}
	}
