const Meja = require("../../src/models/mejaModel");
const mejaController = require("../../src/controllers/mejaController");

const mockRequest = (body = {}, params = {}) => ({
  body,
  params,
});

const mockResponse = () => {
  const res = {};
  res.status = jest.fn().mockReturnValue(res);
  res.json = jest.fn().mockReturnValue(res);
  return res;
};

jest.mock("../../src/models/mejaModel");

describe("Modul 3 - Meja Controller", () => {
  afterEach(() => {
    jest.spyOn(console, 'log').mockImplementation(() => { });
    jest.clearAllMocks();
  });

  describe("createMeja", () => {
    test("harus berhasil membuat meja baru", async () => {
      const mockMeja = {
        tableNumber: 5,
        capacity: 4,
        status: "available",
      };

      Meja.create.mockResolvedValue(mockMeja);

      const req = mockRequest({ tableNumber: 5, capacity: 4 });
      const res = mockResponse();

      await mejaController.createMeja(req, res);

      expect(Meja.create).toHaveBeenCalledWith({
        tableNumber: 5,
        capacity: 4,
      });
      expect(res.status).toHaveBeenCalledWith(201);
      expect(res.json).toHaveBeenCalledWith({
        success: true,
        data: mockMeja,
      });
    });

    test("harus mengembalikan error 400 ketika validasi gagal", async () => {
      const errorMessage = "Validation error";
      Meja.create.mockRejectedValue(new Error(errorMessage));

      const req = mockRequest({ tableNumber: 5 });
      const res = mockResponse();

      await mejaController.createMeja(req, res);

      expect(res.status).toHaveBeenCalledWith(400);
      expect(res.json).toHaveBeenCalledWith({
        success: false,
        error: errorMessage,
      });
    });
  });

  describe("getAllMeja", () => {
    test("harus mengembalikan semua data meja", async () => {
      const mockMejaList = [
        { tableNumber: 1, capacity: 2, status: "available" },
        { tableNumber: 2, capacity: 4, status: "reserved" },
      ];

      const mockSort = jest.fn().mockResolvedValue(mockMejaList);
      const mockThen = jest.fn().mockImplementation(callback => Promise.resolve(callback(mockMejaList)));
      const mockFindResult = { sort: mockSort, then: mockThen };

      Meja.find = jest.fn().mockReturnValue(mockFindResult);

      const req = mockRequest();
      const res = mockResponse();

      await mejaController.getAllMeja(req, res);

      expect(Meja.find).toHaveBeenCalled();
      // Don't test for sort being called specifically, as it may not be in all implementations
      expect(res.status).toHaveBeenCalledWith(200);
      expect(res.json).toHaveBeenCalledWith({
        success: true,
        data: mockMejaList,
      });
    });

    test("harus mengembalikan error 400 ketika terjadi kesalahan database", async () => {
      const errorMessage = "Database error";

      // Create a more flexible mock that handles both implementations
      const mockFindResult = {};

      // For implementations with sort
      const mockSort = jest.fn().mockRejectedValue(new Error(errorMessage));
      mockFindResult.sort = mockSort;

      // For implementations without sort
      mockFindResult.then = jest.fn().mockImplementation(() =>
        Promise.reject(new Error(errorMessage))
      );

      Meja.find = jest.fn().mockReturnValue(mockFindResult);

      const req = mockRequest();
      const res = mockResponse();

      await mejaController.getAllMeja(req, res);

      expect(res.status).toHaveBeenCalledWith(400);
      expect(res.json).toHaveBeenCalledWith({
        success: false,
        error: errorMessage,
      });
    });
  });

  describe("reserveMeja", () => {
    test("harus berhasil mereservasi meja", async () => {
      const tableNumber = "10";
      const customerName = "Susilo Bambang";
      const mockMeja = {
        tableNumber: 10,
        capacity: 4,
        status: "reserved",
        customerName: "Susilo Bambang",
      };

      Meja.findOneAndUpdate.mockResolvedValue(mockMeja);

      const req = mockRequest({ customerName }, { tableNumber });
      const res = mockResponse();

      await mejaController.reserveMeja(req, res);

      expect(Meja.findOneAndUpdate).toHaveBeenCalledWith(
        { tableNumber, status: "available" },
        { status: "reserved", customerName },
        { new: true },
      );
      expect(res.status).toHaveBeenCalledWith(200);
      expect(res.json).toHaveBeenCalledWith({
        success: true,
        data: mockMeja,
      });
    });

    test("harus mengembalikan error 400 ketika nama pelanggan tidak disediakan", async () => {
      const tableNumber = "10";
      const req = mockRequest({}, { tableNumber });
      const res = mockResponse();

      await mejaController.reserveMeja(req, res);

      expect(res.status).toHaveBeenCalledWith(400);
      expect(res.json).toHaveBeenCalledWith({
        success: false,
        error: "Nama pelanggan harus diisi",
      });
    });

    test("harus mengembalikan error 404 ketika meja tidak tersedia", async () => {
      const tableNumber = "10";
      const customerName = "Susilo Bambang";

      Meja.findOneAndUpdate.mockResolvedValue(null);

      const req = mockRequest({ customerName }, { tableNumber });
      const res = mockResponse();

      await mejaController.reserveMeja(req, res);

      expect(res.status).toHaveBeenCalledWith(404);
      expect(res.json).toHaveBeenCalledWith({
        success: false,
        error: "Meja tidak tersedia",
      });
    });
  });

  describe("cancelReservation", () => {
    test("harus berhasil membatalkan reservasi meja", async () => {
      const tableNumber = "15";
      const mockMeja = {
        tableNumber: 15,
        capacity: 4,
        status: "available",
        customerName: "",
      };

      Meja.findOneAndUpdate.mockResolvedValue(mockMeja);

      const req = mockRequest({}, { tableNumber });
      const res = mockResponse();

      await mejaController.cancelReservation(req, res);

      expect(Meja.findOneAndUpdate).toHaveBeenCalledWith(
        { tableNumber, status: "reserved" },
        {
          status: "available",
          customerName: "",
          updatedAt: expect.any(Number),
        },
        { new: true },
      );
      expect(res.status).toHaveBeenCalledWith(200);
      expect(res.json).toHaveBeenCalledWith({
        success: true,
        message: `Reservation for table ${tableNumber} has been cancelled`,
        data: mockMeja,
      });
    });

    test("harus mengembalikan error 404 ketika meja tidak ditemukan", async () => {
      const tableNumber = "99";

      Meja.findOneAndUpdate.mockResolvedValue(null);

      const req = mockRequest({}, { tableNumber });
      const res = mockResponse();

      await mejaController.cancelReservation(req, res);

      expect(res.status).toHaveBeenCalledWith(404);
      expect(res.json).toHaveBeenCalledWith({
        success: false,
        error: "Table not found or not currently reserved",
      });
    });

    test("harus mengembalikan error 400 ketika terjadi kesalahan database", async () => {
      const tableNumber = "15";
      const errorMessage = "Database error";

      Meja.findOneAndUpdate.mockRejectedValue(new Error(errorMessage));

      const req = mockRequest({}, { tableNumber });
      const res = mockResponse();

      await mejaController.cancelReservation(req, res);

      expect(res.status).toHaveBeenCalledWith(400);
      expect(res.json).toHaveBeenCalledWith({
        success: false,
        error: errorMessage,
      });
    });
  });
});
