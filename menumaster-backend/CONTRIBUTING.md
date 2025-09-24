# Contributing to MenuMaster Backend

Thank you for your interest in contributing to MenuMaster Backend! This document provides guidelines and information for contributors.

## Code of Conduct

By participating in this project, you agree to maintain a respectful and inclusive environment for all contributors.

## Getting Started

### Prerequisites

- PHP 8.0 or higher
- MySQL 8.0 or higher
- Composer
- Git
- Basic understanding of MVC architecture and RESTful APIs

### Development Setup

1. Fork the repository
2. Clone your fork:
   ```bash
   git clone https://github.com/your-username/menumaster-backend.git
   cd menumaster-backend
   ```

3. Install dependencies:
   ```bash
   composer install
   ```

4. Set up your environment:
   ```bash
   cp .env.example .env
   # Edit .env with your local database credentials
   ```

5. Set up the database:
   ```bash
   # Create database and import schema
   php setup_analisis_avanzado.php
   ```

## Development Guidelines

### Code Style

- Follow PSR-12 coding standards
- Use meaningful variable and function names
- Add PHPDoc comments for all public methods
- Keep functions small and focused on a single responsibility

### File Structure

- **Controllers**: Handle HTTP requests and responses
- **Models**: Database interactions and business logic
- **Middleware**: Request processing and authentication
- **Utils**: Helper classes and utilities
- **Routes**: API endpoint definitions

### Database Guidelines

- Use prepared statements for all database queries
- Implement proper error handling
- Use transactions for multi-step operations
- Follow naming conventions (snake_case for tables/columns)

### Security Best Practices

- Validate and sanitize all input data
- Use parameterized queries to prevent SQL injection
- Implement proper authentication and authorization
- Never expose sensitive information in error messages
- Use HTTPS in production environments

## Making Changes

### Branch Naming

Use descriptive branch names:
- `feature/add-inventory-tracking`
- `bugfix/fix-order-calculation`
- `hotfix/security-patch`

### Commit Messages

Write clear, descriptive commit messages:
```
feat: add inventory movement tracking

- Implement MovimientoInventarioModel
- Add API endpoints for inventory movements
- Include validation for stock updates
```

### Testing

- Write unit tests for new functionality
- Ensure all existing tests pass
- Test API endpoints with various scenarios
- Verify database operations work correctly

Run tests before submitting:
```bash
composer test
```

### Pull Request Process

1. Create a feature branch from `main`
2. Make your changes with appropriate tests
3. Update documentation if needed
4. Ensure all tests pass
5. Submit a pull request with:
   - Clear description of changes
   - Reference to related issues
   - Screenshots for UI changes (if applicable)

## API Development

### RESTful Conventions

- Use appropriate HTTP methods (GET, POST, PUT, DELETE)
- Return consistent JSON responses
- Use proper HTTP status codes
- Implement pagination for list endpoints

### Response Format

Maintain consistent response structure:
```json
{
    "success": true,
    "message": "Operation completed successfully",
    "data": {...},
    "errors": null
}
```

### Error Handling

- Use appropriate HTTP status codes
- Provide meaningful error messages
- Log errors for debugging
- Never expose sensitive system information

## Database Changes

### Migrations

- Create migration scripts for schema changes
- Include both up and down migrations
- Test migrations on sample data
- Document any data transformations

### Model Updates

- Update model classes for schema changes
- Maintain backward compatibility when possible
- Update related controllers and tests

## Documentation

### Code Documentation

- Add PHPDoc comments for all public methods
- Document complex business logic
- Include usage examples for utility functions

### API Documentation

- Update API documentation for new endpoints
- Include request/response examples
- Document authentication requirements
- Specify required permissions

## Performance Considerations

- Optimize database queries
- Use appropriate indexes
- Implement caching where beneficial
- Monitor memory usage
- Profile slow operations

## Security Review

All contributions undergo security review:

- Input validation and sanitization
- SQL injection prevention
- XSS protection
- Authentication and authorization
- Data encryption requirements

## Questions and Support

- Create an issue for bugs or feature requests
- Use discussions for questions and ideas
- Contact maintainers for security concerns

## Recognition

Contributors will be recognized in:
- CHANGELOG.md for significant contributions
- README.md contributors section
- Release notes for major features

Thank you for contributing to MenuMaster Backend!