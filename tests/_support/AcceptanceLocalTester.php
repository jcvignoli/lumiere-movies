<?php


/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 *
 * @SuppressWarnings(PHPMD)
*/
class AcceptanceLocalTester extends \Codeception\Actor{

	use _generated\AcceptanceLocalTesterActions;

	/**  Use custom trait
	 * 
	 */
	use AcceptanceCustom;

	/** Define custom Local actions here
	 * 
	 */

}
