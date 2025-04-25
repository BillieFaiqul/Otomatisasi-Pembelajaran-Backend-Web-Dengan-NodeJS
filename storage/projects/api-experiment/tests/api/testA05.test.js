const mongoose = require("mongoose");
const request = require("supertest");
const app = require("../../app");
const Product = require("../../models/product.model");

require("dotenv").config();
mongoose.set("strictQuery", true);

const options = {
  showPrefix: false,
  showMatcherMessage: true,
  showStack: true,
};

beforeAll(async () => {
  await connectDB(process.env.MONGODB_URI_TEST).then(
    async () => {
      console.log("Database connected successfully");
      await createProducts();
    },
    (err) => {
      console.log("There is problem while connecting database " + err);
    }
  );
});

describe("DELETE /api/v1/products/:slug", () => {
  it("should delete a product", async () => {
    const product = await Product.findOne({ slug: "product-3" }).lean().exec();
    const res = await request(app).delete("/api/v1/product/product-3");
    expect(
      res.statusCode,
      `Expected status code 200 when requesting to delete a product, but got "${res.statusCode}", the status 200 means that the request has succeeded. Change it in the file "controllers/api/product.controller.js"`
    ).toBe(200);
    expect(
      res.body,
      `Expected the response body to have a property called "message" and the value of that property should be "Product deleted"`,
      options
    ).toHaveProperty("message");
    expect(
      res.body.message,
      `Expected the value of the "message" property to be "Product deleted"`,
      options
    ).toBe("Product deleted");
    expect(
      res.body,
      `Expected the response body to have a property called "product" for the deleted product and the value of that property should be an object`,
      options
    ).toHaveProperty("product");
    expect(
      res.body.product.name,
      `Expected the product deleted to be the same as the product sent to the server but it is not. Make sure that you are using the "findByIdAndDelete" method and that you are passing the correct parameters to it.`,
      options
    ).toBe("Product 3");
    const checkProduct = await Product.findById(product._id).lean().exec();
    expect(
      checkProduct,
      `Expected the product to be deleted from the database but it is not. Make sure that you are using the "findByIdAndDelete" method and that you are passing the correct parameters to it.`,
      options
    ).toBeNull();
    expect(
      res.req.method,
      `Expected the request method to be "DELETE" but it is not`,
      options
    ).toBe("DELETE");
    expect(
      res.type,
      `Expected the response type to be "application/json" but it is not`,
      options
    ).toBe("application/json");
  });

  it("should not delete a product because it does not exist", async () => {
    const res = await request(app).delete("/api/v1/product/product-3");
    expect(
      res.statusCode,
      `Expected status code "404" when requesting to delete a product that does not exist, but got "${res.statusCode}", the status 404 means that the server can not find the requested resource. Change it in the file "controllers/api/product.controller.js"`
    ).toBe(404);
    expect(
      res.body,
      `Expected the response body to have a property called "message" and the value of that property should be "No product found"`,
      options
    ).toHaveProperty("message");
    expect(
      res.body.message,
      `Expected the value of the "message" property to be "No product found"`,
      options
    ).toBe("No product found");
  });

  it("should return error 500", async () => {
    await disconnectDB().then(async () => {
      const res = await request(app).delete("/api/v1/product/product_4");
      expect(
        res.statusCode,
        `Expected status code "500", but got "${res.statusCode}", the "500" is the status code for "Internal Server Error" and it is the status code that we are expecting to get back from the server when we try to delete a product without database connection.`,
        options
      ).toBe(500);
      await connectDB();
    });
  });
});

afterAll(async () => {
  const collections = await mongoose.connection.db.collections();
  for (let collection of collections) {
    await collection.drop();
  }
  await disconnectDB();
});

async function createProducts() {
  await Product.create(
    {
      name: "Product 1",
      price: 100,
      description: "Description 1",
    },
    {
      name: "Product 2",
      price: 200,
      description: "Description 2",
    },
    {
      name: "Product 3",
      price: 1009,
      description: "Description 3",
    }
  );
}

async function connectDB() {
  return mongoose.connect(process.env.MONGODB_URI_TEST, {
    useNewUrlParser: true,
    useUnifiedTopology: true,
  });
}

async function disconnectDB() {
  await mongoose.connection.close();
}
