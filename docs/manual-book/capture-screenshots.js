// E-QMS Manual Book Screenshot Automation
// Playwright script — captures all 21 required screenshots

const { chromium } = require("playwright");
const path = require("path");
const fs = require("fs");

const BASE_URL = "http://127.0.0.1:8000";
const ADMIN_USER = "admin";
const ADMIN_PASS = "admin123";
const OUTPUT_DIR = path.join(__dirname, "screenshots");

if (!fs.existsSync(OUTPUT_DIR)) {
  fs.mkdirSync(OUTPUT_DIR, { recursive: true });
}

const report = [];

async function ss(page, filename, caption) {
  const filepath = path.join(OUTPUT_DIR, filename);
  await page.screenshot({ path: filepath, fullPage: false });
  console.log("PASS: " + filename);
  report.push({ file: filename, status: "PASS", caption, notes: "" });
}

function nf(filename, caption, reason) {
  console.log("NOT FOUND: " + filename + " -- " + reason);
  report.push({ file: filename, status: "NOT FOUND", caption, notes: reason });
}

function du(filename, caption, note) {
  console.log("DIFF UI: " + filename);
  report.push({ file: filename, status: "DIFFERENT UI", caption, notes: note });
}

async function loginAdmin(page) {
  await page.goto(BASE_URL, { waitUntil: "networkidle" });
  await page.fill("input[name=username]", ADMIN_USER);
  await page.fill("input[name=password]", ADMIN_PASS);
  await page.click("button[type=submit]");
  await page.waitForURL("**/admin/**", { timeout: 10000 });
}

async function tryLoginReviewer(page, username, password) {
  await page.goto(BASE_URL, { waitUntil: "networkidle" });
  await page.fill("input[name=username]", username);
  await page.fill("input[name=password]", password);
  await page.click("button[type=submit]");
  await page.waitForURL("**/reviewer/**", { timeout: 8000 });
}

(async () => {
  const browser = await chromium.launch({ headless: true, args: ["--no-sandbox","--disable-dev-shm-usage"] });
  const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
  const page = await context.newPage();

  try {
    // 01 Login
    console.log("--- 01 Login ---");
    await page.goto(BASE_URL, { waitUntil: "networkidle" });
    await ss(page, "01-login.png", "Halaman Login e-QMS");

    // 02 Admin Dashboard
    console.log("--- 02 Admin Dashboard ---");
    await loginAdmin(page);
    await page.waitForTimeout(1000);
    await ss(page, "02-admin-dashboard.png", "Dashboard Admin");

    // 03 Add User
    console.log("--- 03 Add User ---");
    await page.goto(BASE_URL + "/admin/users/create", { waitUntil: "networkidle" });
    await ss(page, "03-add-user.png", "Form Tambah User");

    // 04 Edit User
    console.log("--- 04 Edit User ---");
    await page.goto(BASE_URL + "/admin/users", { waitUntil: "networkidle" });
    const editLink = page.locator("a[href*='/edit']").first();
    if (await editLink.count() > 0) {
      await editLink.click();
      await page.waitForLoadState("networkidle");
      await ss(page, "04-edit-user.png", "Form Edit User");
    } else {
      nf("04-edit-user.png","Form Edit User","No edit link found");
    }

    // 05 Delete User (user list with delete button)
    console.log("--- 05 Delete User ---");
    await page.goto(BASE_URL + "/admin/users", { waitUntil: "networkidle" });
    await ss(page, "05-delete-user.png", "Daftar User dengan Tombol Hapus");
    du("05-delete-user.png","Konfirmasi Penghapusan User","Dialog delete adalah native browser confirm() — tidak bisa di-screenshot. Screenshot menampilkan halaman daftar user dengan tombol hapus.");

    // 06 Business Unit
    console.log("--- 06 Business Unit ---");
    await page.goto(BASE_URL + "/admin/BU", { waitUntil: "networkidle" });
    await page.waitForTimeout(500);
    await ss(page, "06-business-unit.png", "Halaman Business Unit");

    // 07 Upload SOP Form — navigate BU > Divisi > BU Unit > Create
    console.log("--- 07 Upload SOP ---");
    await page.goto(BASE_URL + "/admin/BU/unit/SPBU/create", { waitUntil: "networkidle" });
    const isForm07 = await page.locator("form").count() > 0;
    if (isForm07) {
      await page.waitForTimeout(500);
      await ss(page, "07-upload-sop.png", "Form Upload SOP");
    } else {
      // Try HC create
      await page.goto(BASE_URL + "/admin/support/HC/create", { waitUntil: "networkidle" });
      const isForm07b = await page.locator("form").count() > 0;
      if (isForm07b) {
        await ss(page, "07-upload-sop.png", "Form Upload SOP");
      } else {
        nf("07-upload-sop.png","Form Upload SOP","Form create tidak tersedia");
      }
    }

    // 08 Support Department
    console.log("--- 08 Support ---");
    await page.goto(BASE_URL + "/admin/support", { waitUntil: "networkidle" });
    await ss(page, "08-support-department.png", "Halaman Support Department");

    // 09 Document Detail
    console.log("--- 09 Document Detail ---");
    await page.goto(BASE_URL + "/admin/BU/unit/HC", { waitUntil: "networkidle" });
    let docLink = page.locator("a[href*='/admin/support/document/'], a[href*='/admin/BU/document/']").first();
    if (await docLink.count() === 0) {
      await page.goto(BASE_URL + "/admin/support/HC", { waitUntil: "networkidle" });
      docLink = page.locator("a[href*='/admin/support/document/']").first();
    }
    if (await docLink.count() > 0) {
      const docHref = await docLink.getAttribute("href");
      await page.goto(docHref, { waitUntil: "networkidle" });
      await page.waitForTimeout(800);
      await ss(page, "09-document-detail-timeline.png", "Detail dan Timeline Dokumen");
    } else {
      nf("09-document-detail-timeline.png","Detail Dokumen","Tidak ada dokumen tersedia");
    }

    // 10 Need Revision
    console.log("--- 10 Need Revision ---");
    // Check for revision status documents
    await page.goto(BASE_URL + "/admin/BU", { waitUntil: "networkidle" });
    const revisionEl = page.locator("text=revision").first();
    if (await revisionEl.count() > 0) {
      await ss(page, "10-need-revision.png", "Dokumen Status Need Revision");
    } else {
      // Show edit-revision form of first available doc
      await page.goto(BASE_URL + "/admin/BU/document/1/edit-revision", { waitUntil: "networkidle" });
      const is403 = page.url().includes("403") || await page.locator("text=403").count() > 0;
      if (!is403) {
        await ss(page, "10-need-revision.png", "Halaman Edit Revision Dokumen");
        du("10-need-revision.png","Need Revision","Tidak ada dokumen berstatus revision saat ini. Screenshot menampilkan form edit-revision.");
      } else {
        nf("10-need-revision.png","Need Revision","Tidak ada dokumen berstatus revision. 0 dokumen revision di database.");
      }
    }

    // 11 Upload Revision Form
    console.log("--- 11 Upload Revision ---");
    await page.goto(BASE_URL + "/admin/BU/document/1/edit-revision", { waitUntil: "networkidle" });
    const isRevForm = await page.locator("form").count() > 0 && !page.url().includes("403");
    if (isRevForm) {
      await ss(page, "11-upload-revision.png", "Form Upload Revisi");
    } else {
      nf("11-upload-revision.png","Form Upload Revisi","Tidak ada dokumen berstatus revision untuk diakses");
    }

    // 12 Admin E-Library
    console.log("--- 12 Admin E-Library ---");
    await page.goto(BASE_URL + "/admin/library", { waitUntil: "networkidle" });
    await page.waitForTimeout(500);
    await ss(page, "12-admin-e-library.png", "E-Library Admin");

    // 13 Manual Upload Modal
    console.log("--- 13 Manual Upload ---");
    await page.goto(BASE_URL + "/admin/library?category=support&bu=HC", { waitUntil: "networkidle" });
    await page.waitForTimeout(500);
    const uploadBtn = page.locator("button").filter({ hasText: /tambah sop manual/i }).first();
    if (await uploadBtn.count() > 0) {
      await uploadBtn.click();
      await page.waitForTimeout(600);
      await ss(page, "13-manual-upload.png", "Form Upload Manual E-Library");
    } else {
      // Try different BU
      await page.goto(BASE_URL + "/admin/library?category=divisi&div=RETAIL&bu=SPBU", { waitUntil: "networkidle" });
      await page.waitForTimeout(500);
      const uploadBtn2 = page.locator("button").filter({ hasText: /tambah sop manual/i }).first();
      if (await uploadBtn2.count() > 0) {
        await uploadBtn2.click();
        await page.waitForTimeout(600);
        await ss(page, "13-manual-upload.png", "Form Upload Manual E-Library");
      } else {
        await ss(page, "13-manual-upload.png", "Halaman E-Library (Manual Upload button tidak ditemukan)");
        du("13-manual-upload.png","Manual Upload","Tombol muncul hanya ketika filter bu= aktif dan role admin — screenshot halaman E-Library.");
      }
    }

    // 14 Email Review — CANNOT AUTOMATE
    console.log("--- 14 Email (skip) ---");
    nf("14-review-email.png","Email Notifikasi Review","Email dikirim via MAIL_MAILER=log. Tidak dapat diambil otomatis. Perlu screenshot dari mail client (Mailpit/log viewer) secara manual.");

    // 15–20 as reviewer
    console.log("--- Switching to Reviewer ---");
    await page.goto(BASE_URL, { waitUntil: "networkidle" });

    let reviewerLoggedIn = false;
    const candidates = [
      { user: "nazrikudsi", pass: "password" },
      { user: "garioanora", pass: "password" },
      { user: "laluwandi", pass: "password" },
      { user: "nazrikudsi", pass: "12345678" },
      { user: "reviewer", pass: "password" },
    ];
    for (const c of candidates) {
      try {
        await page.goto(BASE_URL, { waitUntil: "networkidle" });
        await page.fill("input[name=username]", c.user);
        await page.fill("input[name=password]", c.pass);
        await page.click("button[type=submit]");
        await page.waitForURL("**/reviewer/**", { timeout: 5000 });
        reviewerLoggedIn = true;
        console.log("Reviewer logged in: " + c.user);
        break;
      } catch(e) {
        console.log("Login attempt failed for " + c.user + ": " + e.message.slice(0,50));
      }
    }

    if (reviewerLoggedIn) {
      // 15 Review Page
      console.log("--- 15 Review Page ---");
      await page.waitForTimeout(800);
      await ss(page, "15-review-page.png", "Halaman Review — Antrean Reviewer");

      // 16 Review Document
      console.log("--- 16 Review Document ---");
      const rvLink = page.locator("a[href*='/reviewer/show/'], a[href*='reviewer.show']").first();
      const rvLinkHref = await page.locator("a").filter({ hasText: /review/i }).first().getAttribute("href").catch(()=>null);
      
      const anyDocLink = page.locator("a").filter({ hasText: /^review$/i }).first();
      if (await anyDocLink.count() > 0) {
        await anyDocLink.click();
        await page.waitForLoadState("networkidle");
        await page.waitForTimeout(1200);
        await ss(page, "16-review-document.png", "Halaman Detail Review Dokumen");

        // 17 Approve
        console.log("--- 17 Approve ---");
        await ss(page, "17-approve-digital-signature.png", "Area Approve dan Pengesahan Digital");

        // 18 Request Revision
        console.log("--- 18 Request Revision ---");
        // Scroll down to see revision form
        await page.evaluate(() => window.scrollBy(0, 400));
        await page.waitForTimeout(400);
        await ss(page, "18-request-revision.png", "Area Request Revision");
      } else {
        nf("16-review-document.png","Review Document","Tidak ada tombol Review di dashboard reviewer");
        nf("17-approve-digital-signature.png","Approve","Reviewer tidak punya dokumen untuk direview");
        nf("18-request-revision.png","Request Revision","Reviewer tidak punya dokumen untuk direview");
      }

      // 19 User E-Library
      console.log("--- 19 User E-Library ---");
      await page.goto(BASE_URL + "/library", { waitUntil: "networkidle" });
      await page.waitForTimeout(500);
      await ss(page, "19-user-e-library.png", "E-Library User (Reviewer)");

      // 20 Approved SOP Viewer
      console.log("--- 20 Approved SOP ---");
      await page.goto(BASE_URL + "/library?category=support&bu=HC", { waitUntil: "networkidle" });
      await page.waitForTimeout(500);
      const viewBtn = page.locator("button").filter({ hasText: /lihat sop sah/i }).first();
      if (await viewBtn.count() > 0) {
        await viewBtn.click();
        await page.waitForTimeout(2000);
        await ss(page, "20-approved-sop.png", "Viewer SOP Sah");
      } else {
        await page.goto(BASE_URL + "/library?category=divisi&div=RETAIL&bu=SPBU", { waitUntil: "networkidle" });
        await page.waitForTimeout(500);
        const vb2 = page.locator("button").filter({ hasText: /lihat sop sah/i }).first();
        if (await vb2.count() > 0) {
          await vb2.click();
          await page.waitForTimeout(2000);
          await ss(page, "20-approved-sop.png", "Viewer SOP Sah");
        } else {
          nf("20-approved-sop.png","Viewer SOP Sah","Tidak ada SOP aktif di kategori yang tersedia untuk user ini");
        }
      }
    } else {
      nf("15-review-page.png","Review Page","Semua percobaan login reviewer gagal");
      nf("16-review-document.png","Review Document","Login reviewer gagal");
      nf("17-approve-digital-signature.png","Approve","Login reviewer gagal");
      nf("18-request-revision.png","Request Revision","Login reviewer gagal");
      nf("19-user-e-library.png","User E-Library","Login reviewer gagal");
      nf("20-approved-sop.png","Approved SOP","Login reviewer gagal");
    }

    // 21 Workflow Timeline — back as admin
    console.log("--- 21 Workflow ---");
    await loginAdmin(page);
    await page.goto(BASE_URL + "/admin/support/HC", { waitUntil: "networkidle" });
    const docLinkWf = page.locator("a[href*='/admin/support/document/']").first();
    if (await docLinkWf.count() > 0) {
      const wfHref = await docLinkWf.getAttribute("href");
      await page.goto(wfHref, { waitUntil: "networkidle" });
      await page.waitForTimeout(800);
      const tl = page.locator(".border-l, [class*=timeline], .space-y-2").last();
      if (await tl.count() > 0) await tl.scrollIntoViewIfNeeded();
      await page.waitForTimeout(300);
      await ss(page, "21-workflow.png", "Workflow dan Timeline Approval Dokumen");
    } else {
      nf("21-workflow.png","Workflow Timeline","Tidak ada dokumen detail yang bisa diakses");
    }

  } catch (err) {
    console.error("FATAL: " + err.message);
    report.push({ file: "ERROR", status: "ERROR", caption: "Fatal", notes: err.message });
  } finally {
    await browser.close();
  }

  // Report
  const captured = report.filter(r => r.status === "PASS").length;
  const notFoundC = report.filter(r => r.status === "NOT FOUND").length;
  const diffUIc = report.filter(r => r.status === "DIFFERENT UI").length;

  const rows = report.map((r,i) => {
    const no = String(i+1).padStart(2,"0");
    return `| ${no} | ${r.file} | ${r.status} | ${r.caption||""} | ${r.notes||""} |`;
  }).join("\n");

  const md = `# E-QMS Manual Book Screenshot Report

## Summary

| Metric | Count |
|--------|-------|
| Total required | 21 |
| Captured (PASS) | ${captured} |
| Not Found | ${notFoundC} |
| Different UI | ${diffUIc} |

Generated: ${new Date().toLocaleString("id-ID")}

## Screenshot Matrix

| No | File | Status | Feature | Notes |
|----|------|--------|---------|-------|
${rows}
`;

  const reportPath = path.join(__dirname, "SCREENSHOT_REPORT.md");
  fs.writeFileSync(reportPath, md);
  console.log("Report: " + reportPath);
  console.log("PASS: " + captured + " | NOT FOUND: " + notFoundC + " | DIFF UI: " + diffUIc);
})();
