# Contributing to Sirv PHP SDK

First off, thank you for considering contributing to the Sirv PHP SDK!

## Code of Conduct

This project and everyone participating in it is governed by our Code of Conduct. By participating, you are expected to uphold this code.

## How Can I Contribute?

### Reporting Bugs

Before creating bug reports, please check the existing issues to see if the problem has already been reported. When you are creating a bug report, please include as many details as possible:

- **Use a clear and descriptive title** for the issue
- **Describe the exact steps to reproduce the problem**
- **Provide specific examples** to demonstrate the steps
- **Describe the behavior you observed** after following the steps
- **Explain which behavior you expected** to see instead and why
- **Include PHP version** and other relevant environment details

### Suggesting Enhancements

Enhancement suggestions are tracked as GitHub issues. When creating an enhancement suggestion, please include:

- **Use a clear and descriptive title**
- **Provide a step-by-step description** of the suggested enhancement
- **Provide specific examples** to demonstrate the steps
- **Describe the current behavior** and **explain the expected behavior**
- **Explain why this enhancement would be useful**

### Pull Requests

1. Fork the repo and create your branch from `main`
2. If you've added code that should be tested, add tests
3. If you've changed APIs, update the documentation
4. Ensure the test suite passes
5. Make sure your code follows the PSR-12 coding standard
6. Issue that pull request!

## Development Setup

1. Clone your fork:
   ```bash
   git clone https://github.com/your-username/sirv-rest-api-php.git
   cd sirv-rest-api-php
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Run tests:
   ```bash
   composer test
   ```

4. Run code style checks:
   ```bash
   composer cs-check
   ```

5. Run static analysis:
   ```bash
   composer phpstan
   ```

## Coding Standards

- Follow PSR-12 coding standards
- Write meaningful commit messages
- Add PHPDoc blocks to all public methods
- Write tests for new functionality
- Keep backwards compatibility in mind

## Testing

- All new features should include tests
- All bug fixes should include a test that fails without the fix
- Run the full test suite before submitting a pull request

## Documentation

- Update the README.md if you change functionality
- Add PHPDoc blocks to all public methods
- Include examples for new features

## Questions?

Feel free to open an issue with your question or contact us at support@sirv.com.
