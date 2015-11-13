<?php
require('vendor/autoload.php');
class Engine {
    private $ruler;

    private $errCode = 0;

    public function __construct() {
        $this->ruler = Ruler::getInstance();
    }

    public function executeBatch($phases) {
        try{
            $context = Context::getInstance();
            foreach($phases as $phase) {
                $this->executeSinglePhase($phase);
            }
        } catch (Exception $e) {
            $this->errCode = $e->getCode();
            return false;
        }
    }

    public function executeSinglePhase($phase) {
        foreach ($phase->ruleActionPairs as $ruleExpression => $actions) {
            $result = $this->executeRuleActionPair($ruleExpression, $actions);
        }
    }

    public function executeRuleActionPair($ruleExpression, $actions) {
            if ($this->ruler->assert($ruleExpression, Context::getInstance())) {
                foreach($actions as $actionName) {
                    try{
                        sprintf('Executing rule action pair, rule:%s,actionName:%s', $ruleExpression, $actionName);
                        $action = new $actionName();
                        $action->execute();
                    } catch(Exception $e) {
                        sprintf('Exception occured, rule:%s, action:%s',$ruleExpression, $actionName);
                        throw $e;
                    }
                }
            }
    }
}

class Phase {
    public $phaseName;

    public $ruleActionPairs;

    public function __construct($phaseName, $pairs) {
        $this->phaseName = $phaseName;
        $this->ruleActionPairs = $pairs;
    }
}

class Context extends Hoa\Ruler\Context {
    private static $context;

    /**
     * 请求参数。
     */
    private $arrRequest;

    /**
     * 获取实例。
     */
    static function getInstance() {
        if(self::$context == null) {
            self::$context = new Context();
        }
        
        return self::$context;
    }

    /**
     * 获取请求参数的副本。
     */
    public function getRequestParam() {
        $result = $this->arrRequest;
        return $result; 
    }
}

class Ruler extends Hoa\Ruler\Ruler{
    private static $ruler;

    static function getInstance() {
        if(null == self::$ruler) {
            self::$ruler = new Ruler();
            self::$ruler->initRuler();
        }
        return self::$ruler;
    }

    //initialize user defined operator, you can even use Chinese.
    protected function initRuler($phase = '') {
        self::$ruler->getDefaultAsserter()->setOperator('issdk', function () {
            $context = Context::getInstance();
            return $context['sourceFlag'] == 3;
        });
        self::$ruler->getDefaultAsserter()->setOperator('无线端', function () {
            $context = Context::getInstance();
            return $context['sourceFlag'] == 3;
        });

    }
}

class InitContext {
    public function execute() {
        $context = Context::getInstance();
    }
}

class Rule {
    private $ruleExpression;
    public function __construct($ruleExpression) {
        $this->ruleExpression = $ruleExpression;
    }

    public function getRuleExpression() {
        return $this->ruleExpression;
    }
}

class RuleActionPairFactory {
    public static function getRuleActionPair($ruleExpression, $actionName) {
        $rule = new Rule($ruleExpression);
        $pair = new RuleActionPair($rule, $action);
        return $pair;
    } 

    public static function getMultipleRuleActionPair(array $conf) {
        $result = array();
        foreach($conf as $ruleExpression => $actionName) {
            $result[] = self::getRuleActionPair($ruleExpression, $actionName);
        }
        return $result;
    }
}

class RuleActionPair {
    public $rule;
    public $action;
    public function __construct($rule, $action) {
        $this->rule = $rule;
        $this->action = $action;
    }
}

class BasicParamChecker {
    public function execute() {
        return true;
    }
}

class SdkParamChecker {
    public function execute() {
        throw new Exception(' Failed to check param!');
    }
}

class CommonParamChecker {
    public function execute() {
    }
}

class PayActionPhases {
    protected $phases = array(
        'PhaseParamCheck',
    );

    public function __construct($arrPhaseConf) {
        $this->phases = $arrPhaseConf;
    }
}

class PhaseParamCheck {
    public $actions =array(
        'true' => array(
            'InitContext',
            'CommonParamChecker',
        ),
        'sourceFlag in [3]' => array(
            'SdkParamChecker',
        ),
    );

    public function loadFromArray($actions) {
        $this->actions = $actions;
    }
}

