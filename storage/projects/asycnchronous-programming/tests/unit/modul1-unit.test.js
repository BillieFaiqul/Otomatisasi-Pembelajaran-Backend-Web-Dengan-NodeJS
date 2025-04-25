const mongoose = require("mongoose");
const connectDB = require("../../src/config/database");
const errorHandler = require("../../src/middleware/errorHandler");

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

describe("Modul 1 - Unit Tests", () => {
  beforeEach(() => {
    jest.spyOn(console, 'log').mockImplementation(() => { });
    jest.clearAllMocks();
    console.error = jest.fn();
  });

  describe("Error Handler Middleware", () => {
    test("harus menangani error dengan status code yang disediakan", () => {
      const err = new Error("Bad Request Error");
      err.statusCode = 400;
      const req = mockRequest();
      const res = mockResponse();
      const next = jest.fn();

      errorHandler(err, req, res, next);

      expect(console.error).toHaveBeenCalled();
      expect(res.status).toHaveBeenCalledWith(400);
      expect(res.json).toHaveBeenCalledWith({
        success: false,
        error: "Bad Request Error",
      });
    });

    test("harus menggunakan default status code 500 jika tidak disediakan", () => {
      const err = new Error("Server Error Without Status Code");
      const req = mockRequest();
      const res = mockResponse();
      const next = jest.fn();

      errorHandler(err, req, res, next);

      expect(console.error).toHaveBeenCalled();
      expect(res.status).toHaveBeenCalledWith(500);
      expect(res.json).toHaveBeenCalledWith({
        success: false,
        error: "Server Error Without Status Code",
      });
    });
  });

  describe("Custom Error Creation", () => {
    test("harus dapat membuat custom error dengan status code", () => {
      const createCustomError = (message, statusCode) => {
        const error = new Error(message);
        error.statusCode = statusCode;
        return error;
      };

      const notFoundError = createCustomError("Resource not found", 404);
      const validationError = createCustomError("Validation failed", 400);

      expect(notFoundError).toBeInstanceOf(Error);
      expect(notFoundError.message).toBe("Resource not found");
      expect(notFoundError.statusCode).toBe(404);

      expect(validationError).toBeInstanceOf(Error);
      expect(validationError.message).toBe("Validation failed");
      expect(validationError.statusCode).toBe(400);
    });

    test("harus dapat meneruskan error ke middleware error handler", () => {
      const err = new Error("Test Error");
      err.statusCode = 422;
      const req = mockRequest();
      const res = mockResponse();
      const next = jest.fn();

      next(err);
      errorHandler(err, req, res, next);

      expect(next).toHaveBeenCalledWith(err);
      expect(res.status).toHaveBeenCalledWith(422);
      expect(res.json).toHaveBeenCalledWith({
        success: false,
        error: "Test Error",
      });
    });
  });

  describe("Express Middleware Structure", () => {
    test("harus memiliki struktur errorHandler yang benar", () => {
      expect(errorHandler).toBeInstanceOf(Function);
      expect(errorHandler.length).toBe(4);
    });
  });
});
