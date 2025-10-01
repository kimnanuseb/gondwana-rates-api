/**
 * @jest-environment jsdom
 */
import { fireEvent } from "@testing-library/dom";

beforeEach(() => {
  document.body.innerHTML = `
    <form id="rateForm">
      <input type="date" id="arrival" value="2025-10-02" />
      <input type="date" id="departure" value="2025-10-05" />
      <input type="number" id="adults" value="2" />
      <input type="number" id="children" value="1" />
      <button type="submit" id="submitBtn"></button>
    </form>
    <div id="results" class="hidden">
      <div id="summary"></div>
    </div>
  `;

  global.fetch = jest.fn(() =>
    Promise.resolve({
      ok: true,
      json: () =>
        Promise.resolve({
          status: "ok",
          arrival: "2025-10-02",
          departure: "2025-10-05",
          results: [
            {
              unitName: "Kalahari Farmhouse",
              apiResponse: {
                Legs: [
                  {
                    "Total Charge": 1885000,
                    "Effective Average Daily Rate": 65000,
                    "Special Rate Description": "STANDARD RATE CAMPING"
                  }
                ]
              }
            }
          ]
        })
    })
  );
});

test("submitting form renders results", async () => {
  require("../index.html"); // loads script inside your HTML

  const form = document.getElementById("rateForm");
  fireEvent.submit(form);

  // Wait for fetch promise resolution
  await new Promise(process.nextTick);

  const results = document.getElementById("results");
  expect(results.classList.contains("hidden")).toBe(false);

  const summary = document.getElementById("summary").textContent;
  expect(summary).toMatch("Kalahari Farmhouse");
  expect(summary).toMatch("✔ Available");
  expect(summary).toMatch("N$");
});
