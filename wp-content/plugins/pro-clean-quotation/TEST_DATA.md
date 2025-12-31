# Test Data - Spanish Phone Numbers and Postal Codes

This file contains dummy data for testing the Pro Clean Quotation plugin's Spanish validation.

---

## Valid Spanish Phone Numbers

### Local Format (9 digits, starts with 6-9)
```
612345678
687654321
712345678
765432109
812345678
898765432
912345678
987654321
```

### International Format with +34
```
+34612345678
+34687654321
+34712345678
+34765432109
+34812345678
+34851123456
+34898765432
+34912345678
+34987654321
```

### International Format with 0034
```
0034612345678
0034687654321
0034712345678
0034812345678
0034912345678
```

### With Spaces (automatically stripped during validation)
```
+34 612 345 678
+34 687 654 321
612 345 678
712 345 678
812 345 678
```

---

## Valid Spanish Postal Codes

### Madrid Area (28XXX)
```
28001
28002
28010
28013
28020
28045
28080
28099
```

### Barcelona Area (08XXX)
```
08001
08002
08015
08025
08036
08080
08099
```

### Costa del Sol - Málaga Area (29XXX)
```
29600  (Marbella)
29601  (Marbella)
29640  (Fuengirola)
29620  (Torremolinos)
29630  (Benalmádena)
29680  (Estepona)
29001  (Málaga)
29015  (Málaga)
29780  (Nerja)
29770  (Torrox)
29750  (Algarrobo)
```

### Valencia Area (46XXX)
```
46001
46002
46010
46025
46080
```

### Sevilla Area (41XXX)
```
41001
41002
41010
41020
41080
```

### Bilbao Area (48XXX)
```
48001
48002
48010
48015
48080
```

### Other Major Cities
```
15001  (A Coruña)
50001  (Zaragoza)
35001  (Las Palmas)
03001  (Alicante)
30001  (Murcia)
36001  (Pontevedra)
39001  (Santander)
18001  (Granada)
23001  (Jaén)
02001  (Albacete)
```

---

## Invalid Test Cases (Should Fail Validation)

### Invalid Phone Numbers ❌
```
012345678       ❌ starts with 0
512345678       ❌ starts with 5
412345678       ❌ starts with 4
312345678       ❌ starts with 3
61234567        ❌ too short (8 digits)
6123456789      ❌ too long (10 digits)
+34012345678    ❌ country code + starts with 0
+34512345678    ❌ country code + starts with 5
+31612345678    ❌ Netherlands country code
+33612345678    ❌ France country code
+44612345678    ❌ UK country code
abc123456       ❌ contains letters
+346123456789   ❌ too long with country code
+3461234567     ❌ too short with country code
```

### Invalid Postal Codes ❌
```
00000           ❌ starts with 00
53000           ❌ exceeds 52XXX range
60000           ❌ exceeds 52XXX range
99999           ❌ exceeds 52XXX range
2960            ❌ only 4 digits
296000          ❌ 6 digits
ABCDE           ❌ contains letters
29 600          ❌ contains space
29-600          ❌ contains hyphen
```

---

## Quick Copy-Paste Test Set

### Valid Phone + Postal Code Pairs ✅
```
Phone: +34612345678 | Postal Code: 29600
Phone: 687654321    | Postal Code: 28001
Phone: +34712345678 | Postal Code: 08015
Phone: 812345678    | Postal Code: 46001
Phone: +34912345678 | Postal Code: 41010
Phone: +34851123456 | Postal Code: 29640
Phone: 765432109    | Postal Code: 35001
Phone: +34898765432 | Postal Code: 50001
```

### Test Form Submission Data
```json
{
  "customer_name": "Test User",
  "customer_email": "test@example.com",
  "customer_phone": "+34612345678",
  "postal_code": "29600",
  "property_address": "Calle Test 123, Marbella",
  "service_type": "facade",
  "square_meters": 150,
  "building_height": 3,
  "property_type": "residential",
  "surface_material": "brick"
}
```

---

## Testing Scenarios

### Scenario 1: Local Spanish Phone
- **Phone**: `612345678`
- **Postal Code**: `29600`
- **Expected**: ✅ Valid

### Scenario 2: International Format (+34)
- **Phone**: `+34851123456`
- **Postal Code**: `28001`
- **Expected**: ✅ Valid

### Scenario 3: International Format (0034)
- **Phone**: `0034712345678`
- **Postal Code**: `08015`
- **Expected**: ✅ Valid

### Scenario 4: Phone with Spaces
- **Phone**: `+34 612 345 678`
- **Postal Code**: `46001`
- **Expected**: ✅ Valid (spaces automatically removed)

### Scenario 5: Invalid Phone (starts with 0)
- **Phone**: `012345678`
- **Postal Code**: `29600`
- **Expected**: ❌ Invalid phone number

### Scenario 6: Invalid Phone (too short)
- **Phone**: `61234567`
- **Postal Code**: `29600`
- **Expected**: ❌ Invalid phone number (too short)

### Scenario 7: Invalid Postal Code (out of range)
- **Phone**: `+34612345678`
- **Postal Code**: `60000`
- **Expected**: ❌ Invalid postal code (exceeds 52999)

### Scenario 8: Invalid Postal Code (too short)
- **Phone**: `612345678`
- **Postal Code**: `2960`
- **Expected**: ❌ Invalid postal code (must be 5 digits)

---

## Validation Rules Reference

### Phone Number Validation
- **Format**: Local (9 digits) or International (+34/0034 + 9 digits)
- **First Digit**: Must be 6, 7, 8, or 9
- **Total Length**: Exactly 9 digits (after country code)
- **Allowed Characters**: Digits, spaces, plus sign
- **Pattern**: `^((\+34|0034)[6-9][0-9]{8}|[6-9][0-9]{8})$`

### Postal Code Validation
- **Format**: 5 digits
- **Range**: 01000 to 52999
- **Pattern**: `^(0[1-9]|[1-4][0-9]|5[0-2])[0-9]{3}$`
- **Service Area**: All Spain (nationwide)

---

## Notes

- All phone numbers are fictional and generated for testing purposes only
- Postal codes are real Spanish postal code ranges for various cities
- Service area validation is currently set to accept all Spanish postal codes
- JavaScript validation strips spaces automatically before validation
- HTML5 pattern validation runs on form submission
