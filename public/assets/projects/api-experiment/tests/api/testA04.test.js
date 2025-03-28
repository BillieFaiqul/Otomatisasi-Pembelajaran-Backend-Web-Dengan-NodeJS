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

describe("PATCH /api/v1/product/:slug", () => {
  it("should update a product", async () => {
    const FindProduct = await Product.findOne({ slug: "product-3" })
      .lean()
      .exec();
    const res = await request(app).patch("/api/v1/product/product-3").send({
      name: "Product 3 updated",
      price: 109,
      description: "Description 3 updated",
    });
    expect(
      res.statusCode,
      `Expected status code 200, but got "${res.statusCode}", the status 200 means that the request has succeeded. Change it in the file "controllers/api/product.controller.js"`
    ).toBe(200);
    expect(
      res.body,
      `Expected the response body to have a property called "message" and the value of that property should be "Product updated"`,
      options
    ).toHaveProperty("message");
    expect(
      res.body.message,
      `Expected the value of the "message" property to be "Product updated"`,
      options
    ).toBe("Product updated");
    expect(
      res.body,
      `Expected the response body to have a property called "product" and the value of that property should be an object`,
      options
    ).toHaveProperty("product");

    expect(
      res.body.product,
      `Expected the value of the product sent to the server to be updated but it is not. Make sure that you are using the "findByIdAndUpdate" method and that you are passing the correct parameters to it.`
    ).not.toEqual(FindProduct);

    expect(
      res.req.method,
      `Expected the request method to be "PATCH"`,
      options
    ).toBe("PATCH");
    expect(
      res.type,
      `Expected the response content type to be "application/json"`,
      options
    ).toBe("application/json");
  });

  it("should not update a product because it does not exist", async () => {
    const res = await request(app).patch("/api/v1/product/product_4").send({
      name: "Product 3 updated",
      price: 109,
      description: "Description 3",
    });
    expect(
      res.statusCode,
      `Expected status code 404, but got "${res.statusCode}", the status 404 means that the server can not find the requested resource. Change it in the file "controllers/api/product.controller.js"`,
      options
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
      const res = await request(app).patch("/api/v1/product/product_4").send({
        name: "Product 3 updated",
        price: 109,
        description: "Description 3",
      });
      expect(
        res.statusCode,
        `Expected status code "500", but got "${res.statusCode}", the "500" is the status code for "Internal Server Error" and it is the status code that we are expecting to get back from the server when we try to update a product without database connection.`,
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
