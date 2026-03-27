// Default Cartino Configuration for development/testing
export const defaultCartinoConfig = {
  locale: 'it',
  availableLocales: {
    en: 'English',
    it: 'Italiano'
  },
  translations: {
    // Basic translations for testing
    cartino: {
      auth: {
        headings: {
          login: 'Accedi al Pannello di Controllo'
        },
        descriptions: {
          login: 'Inserisci le tue credenziali per accedere'
        }
      },
      menu: {
        dashboard: 'Dashboard',
        products: 'Prodotti',
        orders: 'Ordini',
        customers: 'Clienti',
        collections: 'Collezioni',
        brands: 'Marchi',
        categories: 'Categorie',
        settings: 'Impostazioni'
      },
      actions: {
        save: 'Salva',
        cancel: 'Annulla',
        delete: 'Elimina',
        edit: 'Modifica',
        create: 'Crea',
        update: 'Aggiorna',
        view: 'Visualizza',
        search: 'Cerca'
      },
      status: {
        active: 'Attivo',
        inactive: 'Inattivo',
        draft: 'Bozza',
        archived: 'Archiviato',
        published: 'Pubblicato',
        unpublished: 'Non pubblicato'
      }
    }
  },
  csrf_token: '',
  app_url: 'http://localhost',
  timezone: 'Europe/Rome',
  currency: 'EUR'
};

// Initialize CartinoConfig if not already present
if (typeof window !== 'undefined' && !window.CartinoConfig) {
  console.warn('CartinoConfig not found, initializing with defaults');
  window.CartinoConfig = defaultCartinoConfig;
}
