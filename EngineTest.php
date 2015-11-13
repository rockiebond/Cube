<?php
require_once('Engine.php');
class EngineTest extends PHPUnit_Framework_TestCase {
    private $ruler;
    private $context;

    public function setup() {
        $this->ruler = Ruler::getInstance();
        $this->context = Context::getInstance();
    }
    public function testAssertEmptyRule() {
        $rule = '1';
        $result = $this->ruler->assert($rule, $this->context);
        $this->assertTrue($result);
    }

    public function testSDKRule() {
        $rule = 'sourceFlag in [3]';
        $this->context['sourceFlag'] = 3;
        $result = $this->ruler->assert($rule, $this->context);
        $this->assertTrue($result);
        $this->context['sourceFlag'] = '3';
        $result = $this->ruler->assert($rule, $this->context);
        $this->assertTrue($result);
    }

    public function testGenPairs() {
        $arrConf = array(
            'true' => array(
                'InitContext',
                'CommonParamChecker',
            ),
            'sourceFlag in [3]' => array(
                'SdkParamChecker',
            ),
        );

        $phase = new PhaseParamCheck($arrConf);
        $pairs = RuleActionPairFactory::getMultipleRuleActionPair($phase->actions);
        $this->assertTrue(is_array($pairs));
        $this->assertEquals(2, count($pairs));
    }

    /**
     * @expectedException Exception
     */
    public function testEngineExecute() {
        $arrConf = array(
            'true' => array(
                'InitContext',
                'CommonParamChecker',
            ),
            'sourceFlag in [3]' => array(
                'SdkParamChecker',
            ),
        );
        $phase = new PhaseParamCheck($arrConf);

        $phase = new Phase('paramCheck', $phase->actions);
        $engine = new Engine();
        $result = $engine->executeSinglePhase($phase);
        $this->assertEquals(false, $result);
    }

    /**
     * Test issdk operator.
     */
    public function testOpIsSdk() {
        $rule = 'issdk()';
        $this->context['sourceFlag'] = 3;
        $result = $this->ruler->assert($rule, $this->context);
        $this->assertTrue($result);
    }

    /**
     * Test Chinese char.
     */
    public function testChineseOperator() {
        $rule = '无线端()';
        $this->context['sourceFlag'] = 3;
        $result = $this->ruler->assert($rule, $this->context);
        $this->assertTrue($result);
    }
}


