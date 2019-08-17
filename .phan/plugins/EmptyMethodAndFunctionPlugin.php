<?php declare(strict_types=1);

use ast\Node;
use Phan\Issue;
use Phan\Language\Element\FunctionInterface;
use Phan\Language\Element\Method;
use Phan\PluginV3;
use Phan\PluginV3\PluginAwarePostAnalysisVisitor;
use Phan\PluginV3\PostAnalyzeNodeCapability;

/**
 * Plugin which looks for empty methods/functions
 */
final class EmptyMethodAndFunctionPlugin extends PluginV3 implements PostAnalyzeNodeCapability
{
    public static function getPostAnalyzeNodeVisitorClassName(): string
    {
        return EmptyMethodAndFunctionVisitor::class;
    }
}

/**
 * Visit method/function/closure
 */
final class EmptyMethodAndFunctionVisitor extends PluginAwarePostAnalysisVisitor {

    public function visitMethod(Node $node) : void
    {
        $stmts_node = $node->children['stmts'] ?? null;

        if ($stmts_node && !$stmts_node->children) {
            $method = $this->context->getFunctionLikeInScope($this->code_base);

            if (!$method->isOverriddenByAnother()
                && !$method->isOverride()
                && !$method->isDeprecated()
            ) {
                $this->emitIssue(
                    $this->getIssueTypeForEmptyMethod($method),
                    $method->getNode()->lineno,
                    $method->getName()
                );
            }
        }
    }

    public function visitFuncDecl(Node $node) : void
    {
        $this->analyzeFunction($node);
    }

    public function visitClosure(Node $node) : void
    {
        $this->analyzeFunction($node);
    }

    private function analyzeFunction(Node $node): void
    {
        $stmts_node = $node->children['stmts'] ?? null;

        if ($stmts_node && !$stmts_node->children) {

            $function = $this->context->getFunctionLikeInScope($this->code_base);

            if ( ! $function->isDeprecated())
            {
                if (!$function->isClosure()) {
                    $this->emitIssue(
                        Issue::EmptyFunction,
                        $function->getNode()->lineno,
                        $function->getName()
                    );
                } else {
                    $this->emitIssue(
                        Issue::EmptyClosure,
                        $function->getNode()->lineno
                    );
                }
            }
        }
    }

    private function getIssueTypeForEmptyMethod(FunctionInterface $method) : string
    {
        if (!$method instanceof Method) {
            throw new \InvalidArgumentException("\$method is not an instance of Method");
        }

        if ($method->isPrivate()) {
            return Issue::EmptyPrivateMethod;
        }

        if ($method->isProtected()) {
            return Issue::EmptyProtectedMethod;
        }

        return Issue::EmptyPublicMethod;
    }
}

return new EmptyMethodAndFunctionPlugin();