// Script d'initialisation MongoDB pour le restaurant
// Création de la base de données et des collections

// Basculer vers la base de données restaurant
db = db.getSiblingDB('restaurant_db');

// Créer un utilisateur pour l'application
db.createUser({
  user: 'restaurant_user',
  pwd: 'restaurant_pass',
  roles: [
    {
      role: 'readWrite',
      db: 'restaurant_db'
    }
  ]
});

// Collection pour les menus des restaurants
db.menus.insertMany([
  {
    restaurantId: 1,
    name: "Menu Déjeuner",
    categories: [
      {
        name: "Entrées",
        items: [
          {
            name: "Salade de chèvre chaud",
            description: "Salade verte, croûtons, fromage de chèvre",
            price: 8.50,
            allergens: ["gluten", "lactose"],
            available: true
          },
          {
            name: "Soupe à l'oignon",
            description: "Soupe traditionnelle gratinée",
            price: 7.00,
            allergens: ["lactose"],
            available: true
          }
        ]
      },
      {
        name: "Plats",
        items: [
          {
            name: "Coq au vin",
            description: "Coq mijoté au vin rouge, légumes de saison",
            price: 18.50,
            allergens: ["gluten"],
            available: true
          },
          {
            name: "Saumon grillé",
            description: "Filet de saumon, purée de brocolis",
            price: 22.00,
            allergens: ["poisson"],
            available: true
          }
        ]
      }
    ],
    validFrom: new Date("2025-01-01"),
    validTo: new Date("2025-12-31"),
    active: true,
    createdAt: new Date()
  },
  {
    restaurantId: 2,
    name: "Menu Sushi",
    categories: [
      {
        name: "Sashimi",
        items: [
          {
            name: "Sashimi saumon",
            description: "6 pièces de saumon frais",
            price: 12.00,
            allergens: ["poisson"],
            available: true
          },
          {
            name: "Sashimi thon",
            description: "6 pièces de thon rouge",
            price: 15.00,
            allergens: ["poisson"],
            available: true
          }
        ]
      },
      {
        name: "Maki",
        items: [
          {
            name: "California",
            description: "8 pièces, avocat, concombre, surimi",
            price: 8.50,
            allergens: ["poisson", "crustacés"],
            available: true
          }
        ]
      }
    ],
    validFrom: new Date("2025-01-01"),
    validTo: new Date("2025-12-31"),
    active: true,
    createdAt: new Date()
  }
]);

// Collection pour les réservations
db.reservations.insertMany([
  {
    restaurantId: 1,
    customerName: "Jean Dupont",
    customerEmail: "jean.dupont@email.com",
    customerPhone: "0123456789",
    date: new Date("2025-08-15T19:30:00Z"),
    numberOfGuests: 4,
    specialRequests: "Table près de la fenêtre",
    status: "confirmed",
    createdAt: new Date(),
    updatedAt: new Date()
  },
  {
    restaurantId: 2,
    customerName: "Marie Martin",
    customerEmail: "marie.martin@email.com",
    customerPhone: "0987654321",
    date: new Date("2025-08-16T20:00:00Z"),
    numberOfGuests: 2,
    specialRequests: "Allergie aux crustacés",
    status: "pending",
    createdAt: new Date(),
    updatedAt: new Date()
  }
]);

// Collection pour les avis clients
db.reviews.insertMany([
  {
    restaurantId: 1,
    customerName: "Alice Morel",
    customerEmail: "alice.morel@email.com",
    rating: 5,
    title: "Excellent restaurant !",
    comment: "Service impeccable, cuisine délicieuse. Je recommande vivement !",
    date: new Date("2025-07-20T14:30:00Z"),
    verified: true,
    helpful: 12,
    createdAt: new Date()
  },
  {
    restaurantId: 2,
    customerName: "Pierre Blanc",
    customerEmail: "pierre.blanc@email.com",
    rating: 4,
    title: "Très bon sushi",
    comment: "Poissons frais, service rapide. Juste un peu cher.",
    date: new Date("2025-07-18T20:15:00Z"),
    verified: true,
    helpful: 8,
    createdAt: new Date()
  }
]);

// Collection pour les statistiques et analytics
db.analytics.insertMany([
  {
    restaurantId: 1,
    date: new Date("2025-07-01"),
    reservations: {
      total: 45,
      confirmed: 42,
      cancelled: 3,
      noShow: 0
    },
    revenue: {
      food: 2340.50,
      drinks: 890.20,
      total: 3230.70
    },
    avgRating: 4.6,
    popularItems: [
      { name: "Coq au vin", orders: 18 },
      { name: "Saumon grillé", orders: 15 }
    ]
  },
  {
    restaurantId: 2,
    date: new Date("2025-07-01"),
    reservations: {
      total: 38,
      confirmed: 35,
      cancelled: 2,
      noShow: 1
    },
    revenue: {
      food: 1890.30,
      drinks: 450.80,
      total: 2341.10
    },
    avgRating: 4.3,
    popularItems: [
      { name: "California", orders: 25 },
      { name: "Sashimi saumon", orders: 20 }
    ]
  }
]);

// Création d'index pour optimiser les performances
db.reservations.createIndex({ "restaurantId": 1, "date": 1 });
db.reservations.createIndex({ "customerEmail": 1 });
db.reviews.createIndex({ "restaurantId": 1, "date": -1 });
db.menus.createIndex({ "restaurantId": 1, "active": 1 });
db.analytics.createIndex({ "restaurantId": 1, "date": -1 });

print("Base de données MongoDB initialisée avec succès pour le système de restaurant !");
