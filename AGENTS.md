# AGENTS.md - Coding Guidelines for migphp

## Project Overview

migphp is a database migration tool for PHP projects that uses plain SQL files. It provides a simple CLI interface for managing database schema changes across deployments.

## Development Environment

- **PHP Version**: 8.4.0 minimum required
- **Key Dependencies**: Symfony Console (^7.4), vlucas/phpdotenv (^5.6), ext-pdo
- **Testing**: Pest PHP framework with 100% code coverage requirement
- **Code Quality**: Laravel Pint (formatting), PHPStan (static analysis), Rector (refactoring)

### Setup Commands
```bash
composer install
```

## Build, Lint & Test Commands

### Full Test Suite
```bash
composer test
```
Runs all quality checks: linting, type coverage, typos, unit tests, static analysis, and refactoring checks.

### Individual Quality Gates

**Linting & Formatting:**
```bash
composer lint                    # Format code with Pint
composer test:lint              # Check formatting (fails if unformatted)
```

**Static Analysis:**
```bash
composer test:types             # Run PHPStan analysis (max level)
```

**Testing:**
```bash
composer test:unit              # Run Pest tests with 100% coverage requirement
composer test:type-coverage     # Check type coverage (must be 100%)
```

**Code Quality:**
```bash
composer test:typos             # Run Peck typo checker
composer test:refactor          # Check Rector refactoring suggestions
composer refactor               # Apply Rector refactoring fixes
```

### Running Single Tests

Use Pest's filter option to run specific tests:
```bash
# Run a specific test file
vendor/bin/pest tests/SpecificTest.php

# Run tests matching a pattern
vendor/bin/pest --filter="test_database_connection"

# Run tests for a specific class
vendor/bin/pest --filter="DatabaseTest"
```

## Code Style Guidelines

### PHP Version & Declarations
- **Strict Types**: Always include `declare(strict_types=1);` at the top of every PHP file
- **PHP 8.4+ Features**: Use modern PHP features (readonly classes, promoted properties, etc.)

### File Structure & Organization
- **PSR-4 Autoloading**: `Mig\` namespace maps to `src/` directory
- **Test Namespace**: `Tests\` namespace maps to `tests/` directory
- **Directory Structure**:
  ```
  src/
    Commands/        # CLI commands
    Actions/         # Business logic actions
    ValueObjects/    # Immutable value objects
    Support/         # Utility classes
  tests/             # Pest test files
  migrations/        # SQL migration files
  ```

### Class Design Patterns
- **Final Classes**: Mark classes as `final` unless explicitly designed for inheritance
- **Readonly Classes**: Use `readonly` for immutable data structures
- **Action Classes**: Use dedicated action classes for business logic (follows Command pattern)
- **Value Objects**: Use readonly classes for configuration and data transfer objects

### Constructor Properties
```php
// Preferred: Promoted properties with types
public function __construct(
    public readonly string $host,
    public readonly int $port,
) {}
```

### Import Organization
- **Group Imports**: Group by type (classes, interfaces, traits)
- **Unused Imports**: Remove unused imports (enforced by Rector)
- **Alphabetical Order**: Within groups, sort imports alphabetically

### Naming Conventions

**Classes & Files:**
- PascalCase for class names
- File names match class names exactly
- Action classes: `VerbNounAction` (e.g., `RunMigration`, `StoreMigrationExecuted`)

**Methods:**
- camelCase for method names
- `__invoke()` preferred for command classes
- Descriptive names: `execute()`, `run()`, `handle()`

**Variables:**
- camelCase for local variables
- Short names acceptable in CLI contexts (`$o` for OutputInterface, `$i` for InputInterface)
- Descriptive names for business logic variables

**Constants:**
- SCREAMING_SNAKE_CASE
- Private constants preferred over public when possible

### Type Hints & Return Types
- **Always Required**: Add type hints to all parameters and return types
- **Union Types**: Use `string|int` for multiple possible types
- **Nullable Types**: Use `?Type` for nullable parameters
- **Generic Arrays**: Use `array<string, mixed>` for associative arrays

### Error Handling
- **Early Returns**: Use guard clauses and early returns
- **Exception Types**: Throw specific exceptions with meaningful messages
- **Error Arrays**: Return `[bool, string]` tuples for operation results (success/failure with message)
- **Validation**: Validate inputs at method boundaries

### Formatting Rules (Laravel Pint)
- **Indentation**: 4 spaces (no tabs)
- **Line Length**: No strict limit, but keep readable
- **Trailing Whitespace**: Automatically trimmed
- **Final Newlines**: Required at end of files
- **Excluded Directories**: `postgresql/` and `migrations/` directories are excluded from formatting

### DocBlocks
- **Complex Types**: Use `@var` annotations for complex array types
- **Parameter Documentation**: Only for non-obvious parameters
- **Return Documentation**: Only when return type isn't clear from signature

```php
/**
 * @var array{
 *     host: string,
 *     port: int,
 * } $config
 */
```

### CLI Output
- **Emojis**: Use emojis in console output for visual feedback
- **Consistent Messages**: Use standard prefixes (✅, ❌, ⚠️, ℹ️)
- **Progress Indication**: Show clear progress for long-running operations

### Database Operations
- **PDO Usage**: Direct PDO calls for database operations
- **Error Handling**: Check `exec()` return values and handle failures
- **Prepared Statements**: Not currently used (SQL executed directly)
- **Connection Management**: Singleton pattern for database connections

### Testing Guidelines

**Pest Framework Usage:**
- **Test Structure**: Use `it()` function for test descriptions
- **Assertions**: Use `expect()` fluent API
- **Test Naming**: Descriptive but concise test names
- **Coverage**: 100% line and branch coverage required

**Test Organization:**
```php
it('executes migration successfully', function () {
    // Arrange
    $action = new RunMigration();

    // Act
    $result = $action->execute('/path/to/migration.sql');

    // Assert
    expect($result)->toBe([true, 'success']);
});
```

**Test Types:**
- **Unit Tests**: Test individual classes/methods in isolation
- **Integration Tests**: Test database operations with actual connections
- **Feature Tests**: End-to-end CLI command testing

### CI/CD Integration

**GitHub Actions Workflows:**
- **Tests Workflow**: Runs on push/PR across Ubuntu, macOS, Windows with PHP 8.4
- **Formats Workflow**: Validates code style and types on Ubuntu
- **Dependency Testing**: Tests with both lowest and stable dependencies

**Quality Gates:**
- All checks must pass before merge
- Code coverage must be 100%
- No formatting violations allowed
- Static analysis must pass at max level

### Tooling Configuration

**PHPStan (phpstan.neon.dist):**
- Level: max
- Paths: src/ only
- Reports unmatched ignored errors

**Rector (rector.php):**
- Processes: src/ and tests/
- Sets: deadCode, codeQuality, typeDeclarations, privatization, earlyReturn, strictBooleans
- Skips: AddOverrideAttributeToOverriddenMethodsRector

**Pint (pint.json):**
- Excludes: postgresql/, migrations/
- Follows PSR-12 with project-specific rules

**Pest:**
- Configuration: phpunit.xml.dist
- Tests directory: tests/
- Source directory: src/

### Editor Configuration

**.editorconfig:**
- UTF-8 encoding
- LF line endings
- 4-space indentation (2 spaces for YAML)
- Trim trailing whitespace
- Final newlines required

### External Tool Rules

**Cursor Rules:** None configured (.cursor/ directory or .cursorrules file not found)

**Copilot Instructions:** None configured (.github/copilot-instructions.md not found)

## Additional Best Practices

### Code Review Checklist
- [ ] Strict types declaration present
- [ ] Classes marked final where appropriate
- [ ] Proper type hints on all methods
- [ ] Tests written and passing
- [ ] Code formatting applied
- [ ] Static analysis passes
- [ ] No unused imports
- [ ] Meaningful commit messages

### Performance Considerations
- **Database Connections**: Reuse connections via singleton pattern
- **File Operations**: Validate file existence before operations
- **Memory Usage**: Process migrations sequentially, not in parallel

### Security Notes
- **Environment Variables**: Use .env files for sensitive configuration
- **SQL Injection**: Currently mitigated by file-based migrations (trusted content)
- **Secrets**: Never commit .env files or database credentials

---

This document should be updated when new tooling or standards are adopted. Run `composer test` before committing to ensure all quality gates pass.</content>
<parameter name="filePath">/home/me/code/migphp/mig/AGENTS.md