---
name: eqms-domain
description: Stable business-domain knowledge for PT PKM Group e-QMS.
---

# e-QMS Domain

## Project

Electronic Quality Management System (e-QMS) PT PKM Group.

Used for:

- SOP/QMS document management
- document approval
- digital approval stamping
- revision
- audit trail
- E-Library

## Main document structure

A document may contain:

1. Cover
2. Lembar Pengesahan
3. Isi
4. Lampiran

## Main modules

- Business Unit
- Support
- Reviewer / Approval
- Revision
- E-Library
- User Management

## Approval terminology

Approval has business concepts:

- creator
- reviewer
- final approver

`sequence` represents approval execution order.

`signature_slot` represents physical signature location in the PDF.

They are NOT the same thing.

Physical signature slots:

- sig01 = creator area
- sig02-sig08 = reviewer area
- sig09 = final approval area

Unused signature slots must not cause other signatures to shift.

## E-Library

Top-level structure:

E-Library
├── Business Unit
└── Support

Support must never be nested under SCM.

## PDF

PDF processing is business-critical.

Original uploaded PDFs must be preserved.

QPDF is used as compatibility fallback for PDFs that FPDI cannot parse.

## Source of truth

When determining CURRENT implementation behavior, prioritize:

1. executable source code
2. database schema
3. verified local test
4. current project-state documentation
5. this skill

This skill contains domain knowledge, NOT proof that a feature is currently implemented.

Never infer current implementation from planned requirements in documentation.
