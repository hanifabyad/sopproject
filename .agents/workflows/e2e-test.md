---
description: /e2e-test
---

# e-QMS End-to-End Test

Do not modify production code unless the requested test proves
an existing implementation is broken and the user explicitly
asked for repair.

Before testing:
state the expected workflow.

Test:

UI / request
→ route
→ controller
→ database
→ files
→ next state

Verify:

- HTTP result
- document status
- approval status
- reviewer/current responsibility
- generated PDF
- audit log
- E-Library when applicable

Use local/test data only.

Report every checkpoint:

PASS
FAIL
NOT TESTED

Do not claim ALL PASS if visual output has not been inspected
when visual correctness is part of the requirement.
