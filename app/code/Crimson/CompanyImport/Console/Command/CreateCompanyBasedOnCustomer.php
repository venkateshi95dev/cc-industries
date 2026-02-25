<?php

namespace Crimson\CompanyImport\Console\Command;

use Magento\Company\Api\Data\CompanyInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Company\Api\Data\CompanyInterfaceFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Company\Api\CompanyManagementInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\State;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Area;

class CreateCompanyBasedOnCustomer extends Command
{
    public function __construct(
        protected readonly CompanyRepositoryInterface $companyRepository,
        protected readonly CompanyInterfaceFactory $companyFactory,
        protected readonly CustomerRepositoryInterface $customerRepository,
        protected readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        protected readonly CompanyManagementInterface $companyManagement,
        protected readonly LoggerInterface $logger,
        protected readonly State $state,
        protected readonly ResourceConnection $resourceConnection
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('crimson:create-companies');
        $this->setDescription('Command that reads from a CSV file with customer emails and converts them to companies.');
        $this->addArgument(
            'csvfile',
            InputArgument::REQUIRED,
            'Path to the CSV file to process, relative to Magento root or absolute'
        );
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->state->setAreaCode(Area::AREA_CRONTAB);

        $csvFilePath = $input->getArgument('csvfile');

        if (!file_exists($csvFilePath)) {
            $csvFilePath = getcwd() . DIRECTORY_SEPARATOR . $csvFilePath;
        }

        if (!file_exists($csvFilePath)) {
            $output->writeln('<error>File does not exist: ' . $csvFilePath . '</error>');
            return Command::FAILURE;
        }

        $output->writeln('<info>Reading CSV file: ' . $csvFilePath . '</info>');

        $successCount = 0;
        $skippedCount = 0;
        $missingCustomers = [];

        if (($handle = fopen($csvFilePath, 'r')) !== false) {
            $header = null;
            $rowNumber = 0;

            while (($data = fgetcsv($handle, 0, ',')) !== false) {
                $rowNumber++;

                if (!$header) {
                    $header = $data;
                    if (!in_array('email', $header)) {
                        $output->writeln('<error>Missing required column: email</error>');
                        fclose($handle);
                        return Command::FAILURE;
                    }
                    continue;
                }

                $row = array_combine($header, $data);

                if (empty($row['email'])) {
                    $output->writeln("<comment>Row $rowNumber: Skipping - no email provided</comment>");
                    $skippedCount++;
                    continue;
                }

                $output->writeln("<info>Processing row $rowNumber: {$row['email']}</info>");

                $customer = $this->findCustomerByEmail($row['email']);

                if (!$customer) {
                    $missingCustomers[] = $row['email'];

                    $this->logger->info('Customer not found for company import', [
                        'email' => $row['email']
                    ]);

                    $output->writeln("<comment>  ⚠ Skipped: Customer with email {$row['email']} not found</comment>");
                    $skippedCount++;
                    continue;
                }

                $existingCompany = $this->getCustomerCompany($customer->getId());
                if ($existingCompany) {
                    $this->setCompanyPaymentMethod($existingCompany->getId());
                    $output->writeln("<comment>  ⚠ Customer already has company: {$existingCompany->getCompanyName()} - Payment methods updated if needed</comment>");
                    $skippedCount++;
                    continue;
                }

                try {
                    $company = $this->createCompany($customer);
                    $this->setCompanyPaymentMethod($company->getId());
                    $successCount++;
                    $output->writeln("<info>  ✓ Company '{$company->getCompanyName()}' created with ID: {$company->getId()}</info>");
                } catch (\Exception $e) {
                    $output->writeln("<error>{$e->getMessage()}</error>");
                }
            }
            fclose($handle);
        } else {
            $output->writeln('<error>Unable to open the file.</error>');
            return Command::FAILURE;
        }

        if (!empty($missingCustomers)) {
            $this->logger->warning('Company import - Missing customers', [
                'missing_customers' => $missingCustomers,
                'count' => count($missingCustomers)
            ]);
        }

        $output->writeln('');
        $output->writeln('<info>========================================</info>');
        $output->writeln('<info>Processing Complete:</info>');
        $output->writeln("<info>  Companies created: $successCount</info>");
        $output->writeln("<info>  Skipped: $skippedCount</info>");
        if (!empty($missingCustomers)) {
            $output->writeln("<comment>  Missing customers logged: " . count($missingCustomers) . "</comment>");
        }
        $output->writeln('<info>========================================</info>');

        return Command::SUCCESS;
    }

    protected function findCustomerByEmail(string $email)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('email', $email, 'eq')
            ->create();

        $searchResults = $this->customerRepository->getList($searchCriteria);

        if ($searchResults->getTotalCount() > 0) {
            $items = $searchResults->getItems();
            return reset($items);
        }

        return null;
    }

    protected function createCompany($customer)
    {
        $company = $this->companyFactory->create();

        $customerFullName = trim($customer->getFirstname() . ' ' . $customer->getLastname());

        $company->setCompanyName($customerFullName);
        $company->setCompanyEmail($customer->getEmail());
        $company->setStatus(CompanyInterface::STATUS_APPROVED);
        $company->setSuperUserId($customer->getId());
        $company->setCustomerGroupId($customer->getGroupId());

        $addresses = $customer->getAddresses();
        if (!empty($addresses)) {
            $defaultBilling = null;
            foreach ($addresses as $address) {
                if ($address->isDefaultBilling()) {
                    $defaultBilling = $address;
                    break;
                }
            }

            if (!$defaultBilling && !empty($addresses)) {
                $defaultBilling = reset($addresses);
            }

            if ($defaultBilling) {
                $company->setStreet($defaultBilling->getStreet());
                $company->setCity($defaultBilling->getCity());
                $company->setCountryId($defaultBilling->getCountryId());
                $company->setPostcode($defaultBilling->getPostcode());
                $company->setTelephone($defaultBilling->getTelephone());
                $company->setRegion($defaultBilling->getRegion()->getRegion());
                $company->setRegionId($defaultBilling->getRegionId());
            }
        }

        $savedCompany = $this->companyRepository->save($company);
        $this->companyManagement->assignCustomer($savedCompany->getId(), $customer->getId());

        return $savedCompany;
    }

    protected function getCustomerCompany($customerId)
    {
        return $this->companyManagement->getByCustomerId($customerId);
    }

    protected function setCompanyPaymentMethod($companyId)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName('company_payment');

        $select = $connection->select()
            ->from($tableName)
            ->where('company_id = ?', $companyId);

        $existingPayment = $connection->fetchRow($select);

        if (!$existingPayment) {
            $connection->insert(
                $tableName,
                [
                    'company_id' => $companyId,
                    'applicable_payment_method' => 2,
                    'available_payment_methods' => 'purchaseorder',
                    'use_config_settings' => 0
                ]
            );
        } else if ($existingPayment['applicable_payment_method'] != 2 ||
            $existingPayment['available_payment_methods'] != 'purchaseorder') {
            $connection->update(
                $tableName,
                [
                    'applicable_payment_method' => 2,
                    'available_payment_methods' => 'purchaseorder',
                    'use_config_settings' => 0
                ],
                ['company_id = ?' => $companyId]
            );
        }
    }
}