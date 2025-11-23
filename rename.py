import os

for root, dirs, files in os.walk('.'):
    for file in files:
        if 'ch' in file:
            old_path = os.path.join(root, file)
            new_file = file.replace('ch', 'ch')
            new_path = os.path.join(root, new_file)
            os.rename(old_path, new_path)
            print(f'Renamed {old_path} to {new_path}')
    for dir in dirs:
        if 'ch' in dir:
            old_path = os.path.join(root, dir)
            new_dir = dir.replace('ch', 'ch')
            new_path = os.path.join(root, new_dir)
            os.rename(old_path, new_path)
            print(f'Renamed {old_path} to {new_path}')

