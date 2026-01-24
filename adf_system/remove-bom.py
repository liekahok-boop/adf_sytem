#!/usr/bin/env python3
import os
import glob

def remove_bom_from_file(filepath):
    """Remove UTF-8 BOM from file if it exists"""
    try:
        with open(filepath, 'rb') as f:
            content = f.read()
        
        # Check if file starts with UTF-8 BOM
        if content.startswith(b'\xef\xbb\xbf'):
            # Remove BOM and write back
            with open(filepath, 'wb') as f:
                f.write(content[3:])
            return True
    except Exception as e:
        print(f"Error processing {filepath}: {e}")
    return False

def main():
    php_files = glob.glob('c:\\xampp\\htdocs\\adf_system\\**\\*.php', recursive=True)
    
    fixed_count = 0
    for php_file in php_files:
        if remove_bom_from_file(php_file):
            print(f"Fixed BOM in: {php_file}")
            fixed_count += 1
    
    print(f"\nTotal files fixed: {fixed_count}")

if __name__ == '__main__':
    main()
