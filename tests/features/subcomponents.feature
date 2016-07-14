Feature: Subcomponent exceptions belong to their parent exception classes or not

  Background:
    Given Directory structure with namespaces
    And Application with current path "project/src/Foo/Bar/My"
    And a path containing php classes with namespaces in same path
    And option for disabling parent exception search is "not set"
    And dummy files and folders in all directories
    And existing exception classes in path "project/src/Foo/Bar"
    And existing exception classes in path "project/src/Foo"
    And dummy files and folders in all directories

  Scenario: Application finds main exception classes and set's them as base classes
    When the application is executed
    Then a file named "BadMethodCallException.php" should be created in "/project/src/Foo/Bar/My/Exception/" with content
    """
    <?php
    namespace Foo\Bar\My\Exception;

    use Foo\Bar\Exception\BadMethodCallException as BaseBadMethodCallException;

    /**
     * Generated at %s, %d %s %d %d:%d:%d +0000 by behat
     */
    class BadMethodCallException extends BaseBadMethodCallException implements ExceptionInterface
    {
    }

    """
    And a file named "RuntimeException.php" should be created in "/project/src/Foo/Bar/My/Exception/" with content
    """
    <?php
    namespace Foo\Bar\My\Exception;

    use Foo\Bar\Exception\RuntimeException as BaseRuntimeException;

    /**
     * Generated at %s, %d %s %d %d:%d:%d +0000 by behat
     */
    class RuntimeException extends BaseRuntimeException implements ExceptionInterface
    {
    }

    """
    And a file named "InvalidArgumentException.php" should be created in "/project/src/Foo/Bar/My/Exception/" with content
    """
    <?php
    namespace Foo\Bar\My\Exception;

    use Foo\Bar\Exception\InvalidArgumentException as BaseInvalidArgumentException;

    /**
     * Generated at %s, %d %s %d %d:%d:%d +0000 by behat
     */
    class InvalidArgumentException extends BaseInvalidArgumentException implements ExceptionInterface
    {
    }

    """
    And a file named "ExceptionInterface.php" should be created in "/project/src/Foo/Bar/My/Exception/" with content
    """
    <?php
    namespace Foo\Bar\My\Exception;

    use Foo\Bar\Exception\ExceptionInterface as BaseExceptionInterface;

    /**
     * Generated at %s, %d %s %d %d:%d:%d +0000 by behat
     */
    interface ExceptionInterface extends BaseExceptionInterface
    {
    }

    """

  Scenario: Application finds two main exception class folders with missing exception, set's them as base classes and restores missing ones
    And File "InvalidArgumentException.php" is removed from "project/src/Foo/Bar/Exception/"
    And File "InvalidArgumentException.php" is removed from "project/src/Foo/Exception/"
    When the application is executed
    Then a file named "BadMethodCallException.php" should be created in "/project/src/Foo/Bar/My/Exception/" with content
    """
    <?php
    namespace Foo\Bar\My\Exception;

    use Foo\Bar\Exception\BadMethodCallException as BaseBadMethodCallException;

    /**
     * Generated at %s, %d %s %d %d:%d:%d +0000 by behat
     */
    class BadMethodCallException extends BaseBadMethodCallException implements ExceptionInterface
    {
    }

    """
    And a file named "RuntimeException.php" should be created in "/project/src/Foo/Bar/My/Exception/" with content
    """
    <?php
    namespace Foo\Bar\My\Exception;

    use Foo\Bar\Exception\RuntimeException as BaseRuntimeException;

    /**
     * Generated at %s, %d %s %d %d:%d:%d +0000 by behat
     */
    class RuntimeException extends BaseRuntimeException implements ExceptionInterface
    {
    }

    """
    And a file named "InvalidArgumentException.php" should be created in "/project/src/Foo/Bar/My/Exception/" with content
    """
    <?php
    namespace Foo\Bar\My\Exception;

    use Foo\Bar\Exception\InvalidArgumentException as BaseInvalidArgumentException;

    /**
     * Generated at %s, %d %s %d %d:%d:%d +0000 by behat
     */
    class InvalidArgumentException extends BaseInvalidArgumentException implements ExceptionInterface
    {
    }

    """
    And a file named "ExceptionInterface.php" should be created in "/project/src/Foo/Bar/My/Exception/" with content
    """
    <?php
    namespace Foo\Bar\My\Exception;

    use Foo\Bar\Exception\ExceptionInterface as BaseExceptionInterface;

    /**
     * Generated at %s, %d %s %d %d:%d:%d +0000 by behat
     */
    interface ExceptionInterface extends BaseExceptionInterface
    {
    }

    """
    And File "InvalidArgumentException.php" is restored in "project/src/Foo/Bar/Exception/"
    """
    <?php
    namespace Foo\Bar\Exception;

    use Foo\Exception\InvalidArgumentException as BaseInvalidArgumentException;

    /**
     * Generated at %s, %d %s %d %d:%d:%d +0000 by behat
     */
    class InvalidArgumentException extends BaseInvalidArgumentException implements ExceptionInterface
    {
    }

    """
    And File "InvalidArgumentException.php" is restored in "project/src/Foo/Exception/"
    """
    <?php
    namespace Foo\Exception;

    use InvalidArgumentException as BaseInvalidArgumentException;

    /**
     * Generated at %s, %d %s %d %d:%d:%d +0000 by behat
     */
    class InvalidArgumentException extends BaseInvalidArgumentException implements ExceptionInterface
    {
    }

    """