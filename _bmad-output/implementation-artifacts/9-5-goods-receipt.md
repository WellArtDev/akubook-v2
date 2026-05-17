# Story 9.5: Goods Receipt
**Epic:** 9 | **Story ID:** 9.5 | **Key:** 9-5-goods-receipt | **Priority:** P0

## User Story
**Sebagai** Warehouse Staff, **Saya ingin** receive goods dari supplier, **Sehingga** update inventory

## Acceptance Criteria
- Create GR dari approved PO
- GR number (GR-YYYY-NNNN)
- Partial receipt support
- Quality inspection
- Inventory update (add stock)
- Update PO received quantity

## Notes
- Mirror DO structure (Story 8.5)
- Inventory transaction: type = 'purchase_receipt'
