const Menu = require("../../src/models/menuModel");
const menuController = require("../../src/controllers/menuController");

jest.mock("../../src/models/menuModel");

jest.mock("../../src/controllers/menuController", () => {
  const originalModule = jest.requireActual(
    "../../src/controllers/menuController",
  );

  return {
    ...originalModule,

    withCallback: (promise, callback) => {
      return promise
        .then((data) => callback(null, data))
        .catch((err) => callback(err));
    },
  };
});

describe("Modul 2 - Menu Controller", () => {
  let req, res;

  beforeEach(() => {
    jest.spyOn(console, 'log').mockImplementation(() => { });
    jest.clearAllMocks();

    req = {
      body: {
        name: "Nasi Goreng",
        description: "Nasi goreng dengan telur mata sapi",
        price: 25000,
        category: "main",
        isAvailable: true,
      },
      params: {
        category: "main",
      },
    };

    res = {
      status: jest.fn().mockReturnThis(),
      json: jest.fn(),
    };
  });

  describe("createMenuItem", () => {
    test("harus berhasil membuat menu baru", (done) => {
      const mockSavedMenu = { ...req.body, _id: "menu123" };

      Menu.mockImplementation(() => ({
        save: jest.fn().mockResolvedValue(mockSavedMenu),
      }));

      res.status = jest.fn().mockReturnThis();
      res.json = jest.fn().mockImplementation(() => {
        expect(res.status).toHaveBeenCalledWith(201);
        expect(res.json).toHaveBeenCalledWith(mockSavedMenu);
        done();
      });

      menuController.createMenuItem(req, res);
    });

    test("harus menangani error saat pembuatan menu", (done) => {
      const errorMessage = "Validation error";

      Menu.mockImplementation(() => ({
        save: jest.fn().mockRejectedValue(new Error(errorMessage)),
      }));

      res.status = jest.fn().mockReturnThis();
      res.json = jest.fn().mockImplementation(() => {
        expect(res.status).toHaveBeenCalledWith(500);
        expect(res.json).toHaveBeenCalledWith({ error: errorMessage });
        done();
      });

      menuController.createMenuItem(req, res);
    });
  });

  describe("getAllMenuItems", () => {
    test("harus mengembalikan semua menu", (done) => {
      const mockItems = [
        { name: "Nasi Goreng", price: 25000, category: "main" },
        { name: "Sate Ayam", price: 20000, category: "appetizer" },
      ];

      Menu.find.mockResolvedValue(mockItems);

      res.json = jest.fn().mockImplementation((data) => {
        expect(Menu.find).toHaveBeenCalledWith({});
        expect(data).toEqual(mockItems);
        done();
        return res;
      });

      menuController.getAllMenuItems(req, res);
    });

    test("harus menangani error saat mengambil menu", (done) => {
      const errorMessage = "Database error";

      Menu.find.mockRejectedValue(new Error(errorMessage));

      res.status = jest.fn().mockReturnThis();
      res.json = jest.fn().mockImplementation(() => {
        expect(res.status).toHaveBeenCalledWith(500);
        expect(res.json).toHaveBeenCalledWith({ error: errorMessage });
        done();
        return res;
      });

      menuController.getAllMenuItems(req, res);
    });
  });

  describe("getMenuByCategory", () => {
    test("harus mengembalikan menu berdasarkan kategori", (done) => {
      const mockCategoryItems = [
        { name: "Nasi Goreng", price: 25000, category: "main" },
        { name: "Mie Goreng", price: 23000, category: "main" },
      ];

      Menu.find.mockResolvedValue(mockCategoryItems);

      res.json = jest.fn().mockImplementation((data) => {
        expect(Menu.find).toHaveBeenCalledWith({ category: "main" });
        expect(data).toEqual(mockCategoryItems);
        done();
        return res;
      });

      menuController.getMenuByCategory(req, res);
    });

    test("harus mengembalikan 404 ketika kategori tidak ditemukan", (done) => {
      Menu.find.mockResolvedValue([]);

      res.status = jest.fn().mockReturnThis();
      res.json = jest.fn().mockImplementation(() => {
        expect(Menu.find).toHaveBeenCalledWith({ category: "main" });
        expect(res.status).toHaveBeenCalledWith(404);
        expect(res.json).toHaveBeenCalledWith({
          error: "Menu with category 'main' not found",
        });
        done();
        return res;
      });

      menuController.getMenuByCategory(req, res);
    });
  });
});
