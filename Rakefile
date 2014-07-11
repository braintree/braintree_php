task :default => :test
task :test => %w[test:unit test:integration]

namespace :test do
  desc "run unit tests"
  task :unit do
    run_php_tests("tests/unit")
  end

  desc "run integration tests"
  task :integration do
    run_php_tests("tests/integration")
  end

  desc "run a single test file"
  task :single_test, :file_path do |t, args|
    run_php_test_file(args[:file_path])
  end
end

def run_php_test_file(file_path)
  sh "./vendor/bin/phpunit #{file_path}"
end

def run_php_tests(path)
  Dir.glob(path + "/**/*Test.php").each do |file|
    run_php_test_file(file)
  end
end
