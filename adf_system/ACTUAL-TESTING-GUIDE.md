# ✅ INVESTOR & PROJECT MENU - ACTUAL TESTING GUIDE

## 🚨 CRITICAL: Do This First!

### Step 1: Check Menu Visibility
Open: **http://localhost:8080/adf_system/debug-menu-visibility.php**

You MUST see:
- ✅ isLoggedIn(): TRUE
- ✅ investor: TRUE (green box)
- ✅ project: TRUE (green box)

If investor/project show FALSE (red box) → Permission problem

---

### Step 2: Check System Health
Open: **http://localhost:8080/adf_system/system-health-check.php**

All items MUST be GREEN ✅

---

### Step 3: Verify in Browser

1. **Open Dashboard**: http://localhost:8080/adf_system/
2. **Open DevTools**: Press F12
3. **Go to Console Tab**
4. **Refresh page**: Ctrl+F5

### Expected Console Output:
```
🚀 Narayana Hotel Management System Initialized
🔧 Setting up dropdown toggles...
Found dropdown toggles: 3
Attaching click handler to dropdown #0
Attaching click handler to dropdown #1
Attaching click handler to dropdown #2
✅ Dropdown toggles setup complete
```

If you DON'T see these messages → **JavaScript not loaded!**

---

### Step 4: Test Dropdown Click

1. Keep DevTools open (F12)
2. In sidebar, click "Investor"
3. **Watch Console** → should see "Dropdown clicked!"

### Expected in Console:
```
Dropdown clicked!
Found nav-item.has-submenu
Toggling current dropdown
```

If dropdown doesn't click/respond → **JavaScript not attached!**

---

### Step 5: Verify HTML Structure

1. DevTools open (F12)
2. Click **Inspector** tab
3. Find "Investor" menu in sidebar
4. Right-click → **Inspect**
5. Look for this HTML:

```html
<li class="nav-item has-submenu">
  <a href="javascript:void(0)" class="nav-link dropdown-toggle">
    <i data-feather="briefcase"></i>
    <span>Investor</span>
  </a>
  <ul class="submenu">
    ...
  </ul>
</li>
```

**MUST have**:
- ✅ `class="nav-item has-submenu"`
- ✅ `class="nav-link dropdown-toggle"`
- ✅ `<ul class="submenu">`

If missing → **HTML structure wrong!**

---

### Step 6: Check CSS Loaded

In DevTools Inspector (with Investor menu selected):
1. Look at right panel "Styles"
2. Should see these styles:
   - `.nav-item.has-submenu { position: relative; }`
   - `.nav-link.dropdown-toggle { cursor: pointer; }`
   - `.submenu { max-height: 0; overflow: hidden; }`

If NOT there → **CSS not loaded!**

---

## 📊 Troubleshooting Flowchart

```
Investor Menu NOT showing in sidebar?
├─ YES → Check: http://localhost:8080/adf_system/debug-menu-visibility.php
│        If investor=FALSE → Permission problem
│        If investor=TRUE → HTML not rendering
│
└─ Menu shows but dropdown doesn't open?
   ├─ Check DevTools Console for errors
   ├─ Refresh Ctrl+F5 (clear cache)
   ├─ Check HTML has "has-submenu" class
   ├─ Check CSS is loaded
   └─ Check JavaScript messages in console
```

---

## 🔗 Debug URLs

| URL | Purpose |
|-----|---------|
| http://localhost:8080/adf_system/debug-menu-visibility.php | Check if menus should appear |
| http://localhost:8080/adf_system/debug-html-structure.html | Guide for checking HTML/CSS/JS |
| http://localhost:8080/adf_system/system-health-check.php | System diagnostics |
| http://localhost:8080/adf_system/test-permission-system.php | Test permission system |

---

## ⚡ Quick Test in Console

Copy-paste these in DevTools Console:

### Test 1: Check if dropdown elements exist
```javascript
document.querySelectorAll('.nav-link.dropdown-toggle').length
```
Should return: **3** (Investor, Project, Settings)

### Test 2: Check if event listeners attached
```javascript
// Open console → Click Investor menu
// Should see "Dropdown clicked!" in console
```

### Test 3: Manually trigger dropdown
```javascript
const investorMenu = document.querySelector('.nav-link.dropdown-toggle');
investorMenu.click();
// Should see console messages and menu should open
```

### Test 4: Check CSS styles
```javascript
const submenu = document.querySelector('.submenu');
window.getComputedStyle(submenu).maxHeight
// Should return: "0px" (closed) or "500px" (open)
```

---

## 📝 Report Template

When testing, provide this info:

```
✅ Permission Check (debug-menu-visibility.php):
   - investor: [TRUE/FALSE]
   - project: [TRUE/FALSE]

✅ Console Messages:
   - setupDropdownToggles called: [YES/NO]
   - Found X dropdown toggles: [NUMBER]

✅ HTML Structure:
   - Has "has-submenu" class: [YES/NO]
   - Has "dropdown-toggle" class: [YES/NO]
   - Has "submenu" ul: [YES/NO]

✅ CSS Loaded:
   - Styles visible in Inspector: [YES/NO]

✅ JavaScript Working:
   - "Dropdown clicked!" on click: [YES/NO]
   - "open" class toggles: [YES/NO]

✅ Visual Test:
   - Investor menu shows: [YES/NO]
   - Dropdown opens on click: [YES/NO]
   - Submenu item clickable: [YES/NO]
```

---

**Status**: Waiting for test results
**Date**: 25 Januari 2026
