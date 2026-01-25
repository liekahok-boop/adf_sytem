           </div>
           <!-- End Page Content -->
           
           <!-- Footer -->
           <?php
           // Get custom footer text from settings
           $footerCopyrightSetting = $db->fetchOne("SELECT setting_value FROM settings WHERE setting_key = 'footer_copyright'");
           $footerCopyright = $footerCopyrightSetting['setting_value'] ?? ('© ' . APP_YEAR . ' ' . APP_NAME . '. All rights reserved.');
           
           $footerVersionSetting = $db->fetchOne("SELECT setting_value FROM settings WHERE setting_key = 'footer_version'");
           $footerVersion = $footerVersionSetting['setting_value'] ?? ('Version ' . APP_VERSION);
           ?>
           <footer style="margin-top: 3rem; padding: 2rem 0; border-top: 1px solid var(--bg-tertiary); text-align: center; color: var(--text-muted);">
               <p><?php echo htmlspecialchars($footerCopyright); ?></p>
               <p style="font-size: 0.875rem; margin-top: 0.5rem;"><?php echo htmlspecialchars($footerVersion); ?></p>
           </footer>
       </main>
   </div>
   
   <!-- Main JavaScript -->
   <script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
   
   <!-- End Shift Feature -->
   <script src="<?php echo BASE_URL; ?>/assets/js/end-shift.js"></script>
   
   <!-- Initialize Feather Icons -->
   <script>
       feather.replace();
       
       // Real-time clock update
       function updateClock() {
           const now = new Date();
           
           // Update time (HH:MM:SS)
           const hours = String(now.getHours()).padStart(2, '0');
           const minutes = String(now.getMinutes()).padStart(2, '0');
           const seconds = String(now.getSeconds()).padStart(2, '0');
           const timeString = `${hours}:${minutes}:${seconds}`;
           
           const timeElement = document.getElementById('currentTime');
           if (timeElement) {
               timeElement.textContent = timeString;
           }
           
           // Update date at midnight
           const dateElement = document.getElementById('currentDate');
           if (dateElement && now.getHours() === 0 && now.getMinutes() === 0 && now.getSeconds() === 0) {
               location.reload(); // Reload to update date
           }
       }
       
       // Update clock every second
       setInterval(updateClock, 1000);
       updateClock(); // Initial call
       
       // Dropdown Menu Toggle
       document.querySelectorAll('.nav-item.has-submenu .dropdown-toggle').forEach(toggle => {
           toggle.addEventListener('click', function(e) {
               e.preventDefault();
               const parentItem = this.closest('.nav-item.has-submenu');
               
               // Close other dropdowns
               document.querySelectorAll('.nav-item.has-submenu').forEach(item => {
                   if (item !== parentItem) {
                       item.classList.remove('open');
                   }
               });
               
               // Toggle current dropdown
               parentItem.classList.toggle('open');
           });
       });
   </script>
   
   <!-- html2pdf.js Library for PDF Export -->
   <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
   
   <!-- Additional JavaScript -->
   <?php if (isset($additionalJS)): ?>
       <?php foreach ($additionalJS as $js): ?>
           <script src="<?php echo BASE_URL . '/' . $js; ?>"></script>
       <?php endforeach; ?>
   <?php endif; ?>
   
   <!-- Inline Scripts -->
   <?php if (isset($inlineScript)): ?>
       <script>
           <?php echo $inlineScript; ?>
       </script>
   <?php endif; ?>
</body>
</html>
