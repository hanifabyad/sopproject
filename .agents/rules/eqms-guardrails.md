---
trigger: always_on
---

# e-QMS Workspace Guardrails

You are working on the Electronic Quality Management System (e-QMS)
for PT PKM Group.

Always prioritize stability, minimal changes, and preservation of
existing business logic.

## SCOPE DISCIPLINE

Only modify code directly related to the user's current request.

Never:

- fix unrelated bugs automatically
- refactor unrelated code
- redesign unrelated UI
- modify another module because it "looks similar"
- expand task scope without explicit permission

If another issue is discovered:
REPORT IT.
DO NOT FIX IT unless explicitly requested.

## BEFORE EDITING

Before modifying code:

1. Read the relevant controller/model/view/route.
2. Trace the existing flow.
3. Identify the exact root cause.
4. Explain the minimum proposed change.
5. Confirm the change does not alter unrelated behavior.
6. Then implement.

Prefer minimal patches over broad refactors.

## PROTECTED BUSINESS LOGIC

Do not modify approval business rules unless the task explicitly concerns approval workflow.

Do not modify:

- Business Unit workflow
- Support workflow
- Revision workflow
- E-Library mapping
- QPDF fallback
- stage / signature_slot
- existing approval status behavior

unless explicitly requested.

## PROTECTED PDF STAMPING

Do NOT modify:

- findTextCoordinates()
- drawDigitalStamp()
- stamp dimensions
- signature slot mapping
- QPDF normalization

unless the current task explicitly concerns PDF/stamping.

When modifying PDF logic:

- never overwrite original PDF
- preserve existing signatures
- use temporary files when needed
- clean temporary files
- visually inspect rendered PDF before declaring PASS

Never declare PDF positioning PASS based only on numeric coordinates.

## DATABASE SAFETY

Assume all database operations may be destructive.

Never execute without explicit permission:

- migrate:fresh
- database DROP
- table DROP
- mass DELETE
- TRUNCATE
- destructive production queries

Use local/test data for automated testing.

Prefer SELECT/read operations.

## NETWORK & EMAIL SAFETY

NEVER send a real email, HTTP request, webhook, API request,
or other external communication only for testing unless the user
explicitly authorizes the real external action.

For email tests:
prefer MAIL_MAILER=log or another local mail catcher.

Do not assume testing permission means permission to contact real people.

## DEPENDENCIES

Do not:

- composer require
- npm install new packages
- install system tools
- modify server dependencies

unless explicitly requested.

Existing package commands may be used if needed.

## TESTING

After a code change:

1. Run syntax/lint check.
2. Test only the affected flow.
3. Verify database state when relevant.
4. Verify generated files when relevant.
5. Render and visually inspect PDF/UI when relevant.
6. Report PASS / FAIL honestly.

Never claim "100% PASS" if only code or coordinate calculations
were checked without verifying the observable result.

## TEMPORARY TEST FILES

Temporary scratch scripts may be created for local validation.

Use naming:

scratch\_<purpose>.php

After successful testing:
remove temporary scripts unless the user asks to keep them.

Never remove application files.

## REPORTING

After every implementation report:

- Root cause
- Files changed
- What changed
- What was NOT changed
- Test performed
- PASS / FAIL
- Remaining risks

Do not claim a task is complete if manual verification is still required.
