#!/bin/bash
# Quick test script untuk verify checkout AJAX returns JSON

SITE_URL="https://augoose.co"
ENDPOINT="${SITE_URL}/?wc-ajax=update_order_review"

echo "Testing WooCommerce Checkout AJAX Endpoint..."
echo "URL: ${ENDPOINT}"
echo ""

# Test 1: Check Content-Type
echo "=== Test 1: Content-Type Header ==="
CONTENT_TYPE=$(curl -s -X POST "${ENDPOINT}" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "billing_first_name=Test&billing_country=US" \
  -I | grep -i "content-type" | head -1)

echo "Content-Type: ${CONTENT_TYPE}"
if echo "${CONTENT_TYPE}" | grep -qi "application/json"; then
  echo "✓ PASS: Content-Type is application/json"
else
  echo "✗ FAIL: Content-Type is NOT application/json"
  echo "  This means response is HTML, not JSON!"
fi
echo ""

# Test 2: Check Response Body
echo "=== Test 2: Response Body (first 200 chars) ==="
RESPONSE=$(curl -s -X POST "${ENDPOINT}" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "billing_first_name=Test&billing_country=US")

FIRST_CHARS=$(echo "${RESPONSE}" | head -c 200)
echo "${FIRST_CHARS}..."
echo ""

if echo "${FIRST_CHARS}" | grep -q "^<!doctype\|^<html\|^<div\|^<style"; then
  echo "✗ FAIL: Response starts with HTML (should be JSON)"
  echo "  This means theme hooks are outputting HTML during AJAX"
else
  if echo "${FIRST_CHARS}" | grep -q "^{"; then
    echo "✓ PASS: Response starts with JSON"
  else
    echo "? WARNING: Response doesn't start with { (might be error message)"
  fi
fi
echo ""

# Test 3: Validate JSON
echo "=== Test 3: JSON Validation ==="
if echo "${RESPONSE}" | python3 -m json.tool > /dev/null 2>&1; then
  echo "✓ PASS: Response is valid JSON"
else
  echo "✗ FAIL: Response is NOT valid JSON"
  echo "  First 500 chars:"
  echo "${RESPONSE}" | head -c 500
  echo ""
fi
echo ""

echo "=== Summary ==="
echo "If all tests PASS, checkout AJAX is working correctly."
echo "If any test FAILS, apply the patch and test again."
