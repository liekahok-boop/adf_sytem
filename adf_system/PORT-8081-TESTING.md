# ✅ CORRECT PORT = 8081 (NOT 8080!)

## 🎯 CORRECT URLs FOR TESTING

| Test | URL |
|------|-----|
| **Permission Check** | http://localhost:8081/adf_system/debug-menu-visibility.php |
| **Dashboard** | http://localhost:8081/adf_system/ |
| **System Health** | http://localhost:8081/adf_system/system-health-check.php |
| **HTML Debug Guide** | http://localhost:8081/adf_system/debug-html-structure.html |

---

## 🚀 QUICK START TESTS

### Test 1: Check Permission (2 min)
```
http://localhost:8081/adf_system/debug-menu-visibility.php
```

Expected:
- ✅ investor: TRUE
- ✅ project: TRUE

### Test 2: Check Console (2 min)
1. Go to: http://localhost:8081/adf_system/
2. Press F12 → Console tab
3. Ctrl+F5 (refresh)
4. Look for:
   ```
   🔧 Setting up dropdown toggles...
   Found dropdown toggles: 3
   ```

### Test 3: Click Dropdown (1 min)
1. Klik "Investor" di sidebar
2. DevTools console → should see "Dropdown clicked!"
3. Check if submenu appears

---

## 📝 REPORT TEMPLATE

After testing, please provide:

```
✅ Permission visible?
   - investor: [TRUE/FALSE]
   - project: [TRUE/FALSE]

✅ Console shows setup?
   - setupDropdownToggles message: [YES/NO]
   - Found 3 toggles: [YES/NO]

✅ Dropdown works?
   - Investor menu appears: [YES/NO]
   - Dropdown opens on click: [YES/NO]
   - Submenu visible: [YES/NO]

✅ Any console errors?
   - [PASTE ANY RED ERRORS HERE]
```

---

**Port**: 8081 ✅  
**Status**: Ready for testing
