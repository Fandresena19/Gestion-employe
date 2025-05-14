document.addEventListener('DOMContentLoaded', function() {
        const body = document.querySelector("body"),
              sidebar = body.querySelector(".sidebar"),
              toggle = body.querySelector(".toggle");
        
        toggle.addEventListener('click', () => {
            sidebar.classList.toggle("close");
            // Save sidebar state to localStorage
            if(sidebar.classList.contains("close")) {
                localStorage.setItem('sidebarState', 'closed');
            } else {
                localStorage.setItem('sidebarState', 'open');
            }
        });
      });