# Consolidate index.html.twig Files

✅ **1. Create this TODO.md**

**2. Merge & Edit `templates/index.html.twig`**  
   - Base on users/index features (grid, JS, CSS).  
   - Add welcome section.  
   - Fix paths to UserController routes (user_*).  

**3. Update `src/Controller/HomeController.php`**  
   - Compute stats: totalUsers, totalVoyageurs (non-admin), totalAdmins.  
   - Inject UsersRepository if needed.  
   - Render `'index.html.twig'`, pass users + stats.  

**4. Update `src/Controller/UserController.php`**  
   - Add `#[Route('/', name: 'user_index')]` for users_index compatibility.  
   - Fix redirects, paths (user_new etc.).  
   - Add stubs for exports if missing.  

**5. Delete duplicates**  
   - rm templates/home/index.html.twig  
   - rm templates/users/index.html.twig  

**6. Test & cleanup**  
   - bin/console cache:clear  
   - Visit / : unified dashboard?  
   - Test add/delete etc.

Progress: Step 1/6 complete.

