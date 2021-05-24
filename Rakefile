do_not_cache = "--do-not-cache-result"

task :default => :test
task :test => %w[lint:sniff test:unit test:integration]
task :lint => %w[lint:sniff]

namespace :lint do
  # Usage:
  # rake lint:sniff for a summary view of code smells
  # rake lint:sniff[y] for a more detailed report of smells
  desc "sniff for code smells"
  task :sniff, [:details] do |task, args|
    sh "php ./vendor/bin/phpcs --config-set show_progress 1"
    sh "php ./vendor/bin/phpcs --config-set colors 1"
    sh "php ./vendor/bin/phpcs --config-set php_version 70300"
    if args.details.nil?
      sh "php ./vendor/bin/phpcs --standard=phpcs.xml --report=summary lib tests"
    else
      sh "php ./vendor/bin/phpcs --standard=phpcs.xml lib tests"
    end
  end

  desc "Use Code Beautifier and Fixer (cbf) to auto-format what we can, then sniff for the rest"
  # Usage:
  # rake lint:fix
  task :fix do
    sh "php ./vendor/bin/phpcbf lib tests || true" #always run sniffer after fixing
	  Rake::Task["lint:sniff"].invoke
  end
end

namespace :test do
  desc "print PHP version"
  task :version do
    print_php_version("php")
  end

  # Usage:
  #   rake test:unit
  #   rake test:unit[ConfigurationTest]
  #   rake test:unit[ConfigurationTest,testConstructWithArrayOfCredentials]
  desc "run unit tests"
  task :unit, [:file_name, :test_name] => :lint do |task, args|
    if args.file_name.nil?
      sh "php ./vendor/bin/phpunit --testsuite unit #{do_not_cache}"
    elsif args.test_name.nil?
      sh "./vendor/bin/phpunit #{do_not_cache} tests/unit/#{args.file_name}"
    else
      sh "./vendor/bin/phpunit #{do_not_cache} tests/unit/#{args.file_name} --filter #{args.test_name}"
    end
  end

  # Usage:
  #   rake test:integration
  #   rake test:integration[PlanTest]
  #   rake test:integration[PlanTest,testAll_returnsAllPlans]
  desc "run integration tests"
  task :integration, [:file_name, :test_name] => :lint do |task, args|
    if args.file_name.nil?
      sh "php ./vendor/bin/phpunit --testsuite integration #{do_not_cache}"
    elsif args.test_name.nil?
      sh "./vendor/bin/phpunit #{do_not_cache} tests/integration/#{args.file_name}"
    else
      sh "./vendor/bin/phpunit #{do_not_cache} tests/integration/#{args.file_name} --filter #{args.test_name}"
    end
  end

  desc "run tests under PHP"
  task :php => %w[php:unit php:integration]
end

desc "update the copyright year"
task :copyright, :from_year, :to_year do |t, args|
  sh "find tests lib -type f -name '*.php' -exec sed -i 's/#{args[:from_year]} Braintree/#{args[:to_year]} Braintree/g' {} +"
end

def print_php_version(interpreter)
  sh "#{interpreter} --version"
end
