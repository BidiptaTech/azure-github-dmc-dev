<?php

namespace App\Services\Quotations;

use Twig\Markup;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Sandbox\SecurityNotAllowedMethodError;
use Twig\Sandbox\SecurityNotAllowedPropertyError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityPolicyInterface;

/**
 * Fail-closed Twig sandbox for DMC quotation HTML.
 *
 * Unlike Twig's default SecurityPolicy, extends/use are rejected in 3.12,
 * and object method/property access is denied (including Template).
 *
 * Twig compiles attribute() as GetAttrExpression, the same node as
 * branding.company_name. Denying it would also deny normal array-key access.
 * With a plain-array-only renderer context that is acceptable: attribute()
 * cannot reach object methods or properties because those remain forbidden
 * here, and unknown objects are stripped before Twig runs.
 */
class QuotationTwigSecurityPolicy implements SecurityPolicyInterface
{
    /**
     * @param  list<string>  $allowedTags
     * @param  list<string>  $allowedFilters
     * @param  list<string>  $allowedFunctions
     */
    public function __construct(
        private array $allowedTags,
        private array $allowedFilters,
        private array $allowedFunctions
    ) {
    }

    public function checkSecurity($tags, $filters, $functions): void
    {
        foreach ($tags as $tag) {
            if (! in_array($tag, $this->allowedTags, true)) {
                throw new SecurityNotAllowedTagError(sprintf('Tag "%s" is not allowed.', $tag), $tag);
            }
        }

        foreach ($filters as $filter) {
            if (! in_array($filter, $this->allowedFilters, true)) {
                throw new SecurityNotAllowedFilterError(sprintf('Filter "%s" is not allowed.', $filter), $filter);
            }
        }

        foreach ($functions as $function) {
            if (! in_array($function, $this->allowedFunctions, true)) {
                throw new SecurityNotAllowedFunctionError(sprintf('Function "%s" is not allowed.', $function), $function);
            }
        }
    }

    public function checkMethodAllowed($obj, $method): void
    {
        if ($obj instanceof Markup) {
            return;
        }

        $class = get_class($obj);
        throw new SecurityNotAllowedMethodError(
            sprintf('Calling "%s" method on a "%s" object is not allowed.', $method, $class),
            $class,
            $method
        );
    }

    public function checkPropertyAllowed($obj, $property): void
    {
        if ($obj instanceof Markup) {
            return;
        }

        $class = get_class($obj);
        throw new SecurityNotAllowedPropertyError(
            sprintf('Calling "%s" property on a "%s" object is not allowed.', $property, $class),
            $class,
            $property
        );
    }
}
