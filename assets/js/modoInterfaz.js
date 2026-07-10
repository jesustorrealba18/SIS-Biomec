        // 1. Decirle a Tailwind que usaremos la clase 'dark' manualmente
        tailwind.config = {
            darkMode: 'class', 
        }

        // 2. Leer LocalStorage al instante
        const temaGuardado = localStorage.getItem('sgrd_tema') || 'dark'; // Por defecto tu sistema es oscuro
        if (temaGuardado === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
