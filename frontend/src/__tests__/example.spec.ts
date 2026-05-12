import { describe, it, expect } from 'vitest'

// Example unit test using Vitest
// Tests should be co-located with components or in src/__tests__/

describe('Basic Math Operations', () => {
  it('should add two numbers correctly', () => {
    const add = (a: number, b: number) => a + b
    expect(add(2, 3)).toBe(5)
  })

  it('should handle negative numbers', () => {
    const add = (a: number, b: number) => a + b
    expect(add(-1, -1)).toBe(-2)
  })
})

describe('String Utilities', () => {
  it('should capitalize first letter', () => {
    const capitalize = (str: string) => str.charAt(0).toUpperCase() + str.slice(1)
    expect(capitalize('hello')).toBe('Hello')
  })

  it('should trim whitespace', () => {
    const trim = (str: string) => str.trim()
    expect(trim('  hello  ')).toBe('hello')
  })
})
