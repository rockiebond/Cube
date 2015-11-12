<?php
class engine {
    private $ruler;

    private $errCode = 0;

    public function executeBatch($phases) {
        try{
            $context = Context::getInstance();
            foreach($phases as $phase) {
                $this->executeSinglePhase($phase);
            }
        } catch (ActionException $e) {
            $this->errCode = $e->getErrorCode();
            return false;
        }
    }

    public function executeSinglePhase($phase) {
        foreach ($phase->ruleActionPairs as $rule => $action) {
            $this->executeRuleActionPair($rule, $action);
        }
    }

    public function executeRuleActionPair($rule, $action) {
        if ($ruler->assert($rule, ContextgetInstance())) {
            $action->do();
        }
    }
}

class Context {
    private static $context;

    static function getInstance() {
        if(self::$context == null) {
            self::$context = new Context();
        }
        return self::$context;
    }
}

class Ruler {
    private static $ruler;

    static function getInstance() {
        if(self::$ruler= null) {
            self::$ruler = new Ruler();
        }
        return self::$ruler;
    }

    //initialize user defined operator, you can even use Chinese.
    static function initRuler($phase) {
        // We add the logged() operator.
        self::$ruler->getDefaultAsserter()->setOperator('已登录', function () {
            return Context::getInstance()->loggedIn();
        });
    }
}

class ContextInitAction {
    public function execute() {
        $context = Context::getInstance();
        $context['source_flag'] = Pay_Base_Request::getSourceFlag();

    }
}

interface Action {
    public function execute();
}

interface Ruler {
    public function assert($rule, $context);
}

interface Rule {

}
