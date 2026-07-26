[English](SECURITY.md) · [Русский](SECURITY.ru.md)

# Security Policy

## Reporting a vulnerability

**Please do not open a public issue for security problems.**

Report a vulnerability privately through GitHub's
[private vulnerability reporting](https://github.com/sanya-misharin/api-platform-symfony-modular-skeleton/security/advisories/new)
(the repository's **Security → Report a vulnerability** tab). If that is unavailable, contact the maintainer directly and wait for a reply before disclosing anything publicly.

Please include:

- affected version / commit and environment;
- steps to reproduce (a minimal proof of concept if possible);
- impact you believe it has.

You can expect an initial acknowledgement within a few business days. Once a fix is available, the advisory is published and credit is given unless you prefer to stay anonymous.

## Supported versions

This is a **starter template**, not a versioned product: the latest `main` is what receives fixes. A project bootstrapped from this skeleton should define its own supported-versions policy and replace the reporting contact above with its own.

## Scope

Report anything that could compromise a project built on this skeleton — for example: authentication/authorization bypass, injection (SQL/DQL, command), sensitive-data exposure, insecure defaults in the shipped configuration, or a vulnerable pinned dependency. Findings that require already-privileged access, or that only affect the demonstration `Example` module (deleted in real projects), are lower priority.
