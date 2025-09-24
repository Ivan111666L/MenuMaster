# GitHub Setup Guide for MenuMaster Backend

This guide will help you upload the MenuMaster Backend project to GitHub and set it up for collaboration.

## Prerequisites

- Git installed on your system
- GitHub account
- Basic knowledge of Git commands

## Step 1: Create GitHub Repository

1. Go to [GitHub](https://github.com) and log in to your account
2. Click the "+" icon in the top right corner and select "New repository"
3. Fill in the repository details:
   - **Repository name**: `menumaster-backend`
   - **Description**: "Backend API for MenuMaster restaurant management system"
   - **Visibility**: Choose Public or Private based on your needs
   - **DO NOT** initialize with README, .gitignore, or license (we already have these)

## Step 2: Connect Local Repository to GitHub

After creating the repository on GitHub, run these commands in your project directory:

```bash
# Add the remote origin (replace YOUR_USERNAME with your GitHub username)
git remote add origin https://github.com/YOUR_USERNAME/menumaster-backend.git

# Push the code to GitHub
git branch -M main
git push -u origin main
```

## Step 3: Repository Settings

### Branch Protection (Recommended)
1. Go to Settings → Branches in your GitHub repository
2. Add a branch protection rule for `main`:
   - Require pull request reviews before merging
   - Require status checks to pass before merging
   - Restrict pushes to matching branches

### Secrets Configuration
If you plan to use GitHub Actions for CI/CD, add these secrets in Settings → Secrets and variables → Actions:

- `DB_HOST`: Database host
- `DB_NAME`: Database name
- `DB_USER`: Database username
- `DB_PASS`: Database password
- `JWT_SECRET`: JWT secret key

## Step 4: Collaboration Setup

### Adding Collaborators
1. Go to Settings → Collaborators
2. Click "Add people"
3. Enter GitHub usernames or email addresses

### Issue Templates
Consider creating issue templates in `.github/ISSUE_TEMPLATE/` for:
- Bug reports
- Feature requests
- Security vulnerabilities

### Pull Request Template
Create `.github/pull_request_template.md` for consistent PR descriptions.

## Step 5: Documentation Updates

After uploading to GitHub:

1. Update the README.md with your actual GitHub repository URL
2. Add badges for build status, license, etc.
3. Update any documentation links to point to the GitHub repository

## Step 6: Continuous Integration (Optional)

Consider setting up GitHub Actions for:
- Automated testing
- Code quality checks
- Security scanning
- Deployment to staging/production

Example workflow file: `.github/workflows/ci.yml`

## Security Considerations

- Never commit sensitive data (passwords, API keys, etc.)
- Use environment variables for configuration
- Enable security alerts in repository settings
- Regularly update dependencies
- Use branch protection rules

## Next Steps

1. Create your GitHub repository
2. Push your code using the commands above
3. Set up branch protection
4. Add collaborators if needed
5. Configure any CI/CD workflows
6. Update documentation with GitHub URLs

## Support

For issues with this setup:
1. Check GitHub's documentation
2. Review the CONTRIBUTING.md file
3. Create an issue in the repository

---

**Note**: This project is ready for GitHub upload with proper documentation, security practices, and code quality standards.