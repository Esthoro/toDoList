# Contributing to ToDo & Co

Thank you for your interest in contributing to **ToDo & Co**! This document explains how to collaborate on this project and the coding best practices to follow.

---

## Collaboration and Best Practices

### Procedure for Contributing

1. **Create a feature branch**  
```bash
git checkout -b feature/your_feature_name
```

2. **Develop and test locally**
- Implement your feature or fix.
- Ensure your code works as expected.

3. **Add tests**
- Include unit and functional tests if necessary.
- Follow the existing test structure.

4. **Commit changes**
- Use clear and descriptive commit messages.

```bash
git add .
git commit -m "Add feature XYZ: short description"
```

5. **Open a Pull Request**
- Submit a PR for code review.
- Ensure all checks pass before merging.

6. **Quality Rules**
- Follow Symfony standards and PSR-12 coding style.
- Never store passwords in plain text – use UserPasswordHasherInterface for hashing.
- Protect sensitive routes using the appropriate roles.
- Enable CSRF protection on all sensitive forms.
- Add tests for new features or critical modifications.

7. **Testing and Validation**
- Provide unit and functional tests following the existing test examples.
- Validate code quality using SymfonyInsight or equivalent tools.
- Audit performance using tools like SymfonyProfiler.

By following these guidelines, you help maintain the quality, security, and performance of ToDo & Co. Thank you for your contribution!
