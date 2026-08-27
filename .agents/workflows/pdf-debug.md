---
description: /pdf-debug
---

# PDF Debug

Focus only on PDF processing.

Do not modify workflow/business logic.

Inspect:

- original PDF
- page size
- MediaBox
- CropBox
- rotation
- PDF version
- FPDI compatibility
- QPDF normalization
- anchor extraction
- transform matrices
- signature slot
- output PDF

Never overwrite original PDF.

When debugging signature placement:

1. Extract anchor data.
2. Show raw coordinate data.
3. Explain coordinate transform.
4. Create temporary debug output.
5. Render output to image.
6. Inspect visually.
7. Only then propose production fix.

Numerical correctness alone is not visual proof.
