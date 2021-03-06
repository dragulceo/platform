<?php

namespace Oro\Bundle\UserBundle\Entity;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use JMS\Serializer\Annotation as JMS;

use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;

use Oro\Bundle\EmailBundle\Entity\EmailOwnerInterface;
use Oro\Bundle\EmailBundle\Model\EmailHolderInterface;
use Oro\Bundle\EmailBundle\Entity\EmailOrigin;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;

use Oro\Bundle\TagBundle\Entity\Tag;
use Oro\Bundle\TagBundle\Entity\Taggable;

use Oro\Bundle\ImapBundle\Entity\ImapEmailOrigin;
use Oro\Bundle\LocaleBundle\Model\FullNameInterface;
use Oro\Bundle\NotificationBundle\Entity\NotificationEmailInterface;

use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;

use Oro\Bundle\UserBundle\Security\AdvancedApiUserInterface;
use Oro\Bundle\UserBundle\Model\ExtendUser;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @ORM\Entity(repositoryClass="Oro\Bundle\UserBundle\Entity\Repository\UserRepository")
 * @ORM\Table(name="oro_user")
 * @ORM\HasLifecycleCallbacks()
 * @Oro\Loggable
 * @Config(
 *      routeName="oro_user_index",
 *      routeView="oro_user_view",
 *      defaultValues={
 *          "entity"={"icon"="icon-user"},
 *          "ownership"={
 *              "owner_type"="BUSINESS_UNIT",
 *              "owner_field_name"="owner",
 *              "owner_column_name"="business_unit_owner_id",
 *              "organization_field_name"="organization",
 *              "organization_column_name"="organization_id"
 *          },
 *          "dataaudit"={"auditable"=true},
 *          "security"={
 *              "type"="ACL",
 *              "group_name"=""
 *          },
 *          "form"={
 *              "form_type"="oro_user_select",
 *              "grid_name"="users-select-grid"
 *          }
 *      }
 * )
 * @JMS\ExclusionPolicy("ALL")
 */
class User extends ExtendUser implements
    AdvancedUserInterface,
    \Serializable,
    Taggable,
    EmailOwnerInterface,
    EmailHolderInterface,
    FullNameInterface,
    NotificationEmailInterface,
    AdvancedApiUserInterface
{
    const ROLE_DEFAULT = 'ROLE_USER';
    const ROLE_ADMINISTRATOR = 'ROLE_ADMINISTRATOR';
    const ROLE_ANONYMOUS = 'IS_AUTHENTICATED_ANONYMOUSLY';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMS\Type("integer")
     * @JMS\Expose
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, unique=true)
     * @JMS\Type("string")
     * @JMS\Expose
     * @Oro\Versioned
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "identity"=true
     *          }
     *      }
     * )
     */
    protected $username;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, unique=true)
     * @JMS\Type("string")
     * @JMS\Expose
     * @Oro\Versioned
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $email;

    /**
     * Name prefix
     *
     * @var string
     *
     * @ORM\Column(name="name_prefix", type="string", length=255, nullable=true)
     * @JMS\Type("string")
     * @JMS\Expose
     * @Oro\Versioned
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $namePrefix;

    /**
     * First name
     *
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=255, nullable=true)
     * @JMS\Type("string")
     * @JMS\Expose
     * @Oro\Versioned
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $firstName;

    /**
     * Middle name
     *
     * @var string
     *
     * @ORM\Column(name="middle_name", type="string", length=255, nullable=true)
     * @JMS\Type("string")
     * @JMS\Expose
     * @Oro\Versioned
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $middleName;

    /**
     * Last name
     *
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=255, nullable=true)
     * @JMS\Type("string")
     * @JMS\Expose
     * @Oro\Versioned
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $lastName;

    /**
     * Name suffix
     *
     * @var string
     *
     * @ORM\Column(name="name_suffix", type="string", length=255, nullable=true)
     * @JMS\Type("string")
     * @JMS\Expose
     * @Oro\Versioned
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $nameSuffix;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="birthday", type="date", nullable=true)
     * @JMS\Type("DateTime")
     * @JMS\Expose
     * @Oro\Versioned
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $birthday;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     * @JMS\Type("boolean")
     * @JMS\Expose
     * @Oro\Versioned
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $enabled = true;

    /**
     * The salt to use for hashing
     *
     * @var string
     *
     * @ORM\Column(type="string")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          },
     *          "email"={
     *              "available_in_template"=false
     *          }
     *      }
     * )
     */
    protected $salt;

    /**
     * Encrypted password. Must be persisted.
     *
     * @var string
     *
     * @ORM\Column(type="string")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          },
     *          "email"={
     *              "available_in_template"=false
     *          }
     *      }
     * )
     */
    protected $password;

    /**
     * Plain password. Used for model validation. Must not be persisted.
     *
     * @var string
     */
    protected $plainPassword;

    /**
     * Random string sent to the user email address in order to verify it
     *
     * @var string
     *
     * @ORM\Column(name="confirmation_token", type="string", nullable=true)
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $confirmationToken;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="password_requested", type="datetime", nullable=true)
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $passwordRequestedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_login", type="datetime", nullable=true)
     * @JMS\Type("DateTime")
     * @JMS\Expose
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $lastLogin;

    /**
     * @var int
     *
     * @ORM\Column(name="login_count", type="integer", options={"default"=0, "unsigned"=true})
     */
    protected $loginCount;

    /**
     * @var BusinessUnit
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\BusinessUnit", cascade={"persist"})
     * @ORM\JoinColumn(name="business_unit_owner_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $owner;

    /**
     * @var Role[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Role")
     * @ORM\JoinTable(name="oro_user_access_role",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     * @Oro\Versioned("getLabel")
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={"auditable"=true},
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $roles;

    /**
     * @var Group[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Group")
     * @ORM\JoinTable(name="oro_user_access_group",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     * @Oro\Versioned("getName")
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $groups;

    /**
     * @var UserApi[]|ArrayCollection
     *
     * @ORM\OneToMany(
     *  targetEntity="UserApi", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=true, fetch="EXTRA_LAZY"
     * )
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          },
     *          "email"={
     *              "available_in_template"=false
     *          }
     *      }
     * )
     */
    protected $apiKeys;

    /**
     * @var Status[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Status", mappedBy="user")
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    protected $statuses;

    /**
     * @var Status
     *
     * @ORM\OneToOne(targetEntity="Status")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id", nullable=true)
     */
    protected $currentStatus;

    /**
     * @var Email[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Email", mappedBy="user", orphanRemoval=true, cascade={"persist"})
     */
    protected $emails;

    /**
     * @var Tag[]
     *
     */
    protected $tags;

    /**
     * @var BusinessUnit[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Oro\Bundle\OrganizationBundle\Entity\BusinessUnit", inversedBy="users")
     * @ORM\JoinTable(name="oro_user_business_unit",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="business_unit_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     * @Oro\Versioned("getName")
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $businessUnits;

    /**
     * @var ArrayCollection Organization[]
     *
     * @ORM\ManyToMany(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization", inversedBy="users")
     * @ORM\JoinTable(name="oro_user_organization",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $organizations;

    /**
     * @var EmailOrigin[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Oro\Bundle\EmailBundle\Entity\EmailOrigin", cascade={"all"})
     * @ORM\JoinTable(name="oro_user_email_origin",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="origin_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    protected $emailOrigins;

    /**
     * @var \DateTime $createdAt
     *
     * @ORM\Column(type="datetime")
     * @ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.ui.created_at"
     *          }
     *      }
     * )
     */
    protected $createdAt;

    /**
     * @var \DateTime $updatedAt
     *
     * @ORM\Column(type="datetime")
     * @ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.ui.updated_at"
     *          }
     *      }
     * )
     */
    protected $updatedAt;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $organization;

    public function __construct()
    {
        parent::__construct();

        $this->salt          = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $this->roles         = new ArrayCollection();
        $this->groups        = new ArrayCollection();
        $this->statuses      = new ArrayCollection();
        $this->emails        = new ArrayCollection();
        $this->businessUnits = new ArrayCollection();
        $this->emailOrigins  = new ArrayCollection();
        $this->organizations = new ArrayCollection();
        $this->apiKeys       = new ArrayCollection();
    }

    /**
     * Serializes the user.
     * The serialized data have to contain the fields used by the equals method and the username.
     *
     * @return string
     */
    public function serialize()
    {
        return serialize(
            [
                $this->password,
                $this->salt,
                $this->username,
                $this->enabled,
                $this->confirmationToken,
                $this->id,
            ]
        );
    }

    /**
     * Unserializes the user
     *
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        list(
            $this->password,
            $this->salt,
            $this->username,
            $this->enabled,
            $this->confirmationToken,
            $this->id
            ) = unserialize($serialized);
    }

    /**
     * Removes sensitive data from the user.
     */
    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    /**
     * Get entity class name.
     * TODO: Remove this temporary solution for get 'view' route in twig after EntityConfigBundle is finished
     * @return string
     */
    public function getClass()
    {
        return 'Oro\Bundle\UserBundle\Entity\User';
    }

    /**
     * Get names of fields contain email addresses
     *
     * @return string[]|null
     */
    public function getEmailFields()
    {
        return ['email'];
    }

    /**
     * Returns the user unique id.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * {@inheritDoc}
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Return first name
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Return last name
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Return middle name
     *
     * @return string
     */
    public function getMiddleName()
    {
        return $this->middleName;
    }

    /**
     * Return name prefix
     *
     * @return string
     */
    public function getNamePrefix()
    {
        return $this->namePrefix;
    }

    /**
     * Return name suffix
     *
     * @return string
     */
    public function getNameSuffix()
    {
        return $this->nameSuffix;
    }

    /**
     * Return birthday
     *
     * @return \DateTime
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * {@inheritDoc}
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Gets the encrypted password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * {@inheritDoc}
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfirmationToken()
    {
        return $this->confirmationToken;
    }

    /**
     * Gets the timestamp that the user requested a password reset.
     *
     * @return null|\DateTime
     */
    public function getPasswordRequestedAt()
    {
        return $this->passwordRequestedAt;
    }

    /**
     * Gets the last login time.
     *
     * @return \DateTime
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    /**
     * Gets login count number.
     *
     * @return int
     */
    public function getLoginCount()
    {
        return $this->loginCount;
    }

    /**
     * Get user created date/time
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Get user last update date/time
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @return UserApi[]
     */
    public function getApiKeys()
    {
        return $this->apiKeys->count() ? $this->apiKeys : uniqid('undefined');
    }

    /**
     * Add UserApi to User
     *
     * @param  UserApi $api
     *
     * @return User
     */
    public function addApiKey(UserApi $api)
    {
        $this->apiKeys[] = $api;

        $api->setUser($this);

        return $this;
    }

    /**
     * Delete UserApi from User
     *
     * @param  UserApi $api
     *
     * @return User
     */
    public function removeApiKey(UserApi $api)
    {
        $this->apiKeys->removeElement($api);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    public function isAccountNonExpired()
    {
        return true;
    }

    public function isAccountNonLocked()
    {
        return $this->isEnabled();
    }

    public function isPasswordRequestNonExpired($ttl)
    {
        return $this->getPasswordRequestedAt() instanceof \DateTime
        && $this->getPasswordRequestedAt()->getTimestamp() + $ttl > time();
    }

    /**
     * @param int $id
     *
     * @return mixed
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param  string $username New username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @param  string $email New email value
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @param  string $firstName [optional] New first name value. Null by default.
     *
     * @return User
     */
    public function setFirstName($firstName = null)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @param  string $lastName [optional] New last name value. Null by default.
     *
     * @return User
     */
    public function setLastName($lastName = null)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Set middle name
     *
     * @param string $middleName
     *
     * @return User
     */
    public function setMiddleName($middleName)
    {
        $this->middleName = $middleName;

        return $this;
    }

    /**
     * Set name prefix
     *
     * @param string $namePrefix
     *
     * @return User
     */
    public function setNamePrefix($namePrefix)
    {
        $this->namePrefix = $namePrefix;

        return $this;
    }

    /**
     * Set name suffix
     *
     * @param string $nameSuffix
     *
     * @return User
     */
    public function setNameSuffix($nameSuffix)
    {
        $this->nameSuffix = $nameSuffix;

        return $this;
    }

    /**
     *
     * @param  \DateTime $birthday [optional] New birthday value. Null by default.
     *
     * @return User
     */
    public function setBirthday(\DateTime $birthday = null)
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * @param  bool $enabled User state
     *
     * @return User
     */
    public function setEnabled($enabled)
    {
        $this->enabled = (boolean)$enabled;

        return $this;
    }

    /**
     * @param string $salt
     *
     * @return User
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * @param  string $password New encoded password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @param  string $password New password as plain string
     *
     * @return User
     */
    public function setPlainPassword($password)
    {
        $this->plainPassword = $password;

        return $this;
    }

    /**
     * Set confirmation token.
     *
     * @param  string $token
     *
     * @return User
     */
    public function setConfirmationToken($token)
    {
        $this->confirmationToken = $token;

        return $this;
    }

    /**
     * @param  \DateTime $time [optional] New password request time. Null by default.
     *
     * @return User
     */
    public function setPasswordRequestedAt(\DateTime $time = null)
    {
        $this->passwordRequestedAt = $time;

        return $this;
    }

    /**
     * @param  \DateTime $time New login time
     *
     * @return User
     */
    public function setLastLogin(\DateTime $time)
    {
        $this->lastLogin = $time;

        return $this;
    }

    /**
     * @param  int $count New login count value
     *
     * @return User
     */
    public function setLoginCount($count)
    {
        $this->loginCount = $count;

        return $this;
    }

    /**
     * @param \DateTime $createdAt
     *
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @param \DateTime $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Returns the user roles merged with associated groups roles
     *
     * @return Role[] The array of roles
     */
    public function getRoles()
    {
        $roles = $this->getRolesCollection()->toArray();

        /** @var Group $group */
        foreach ($this->getGroups() as $group) {
            $roles = array_merge($roles, $group->getRoles()->toArray());
        }

        return array_unique($roles);
    }

    /**
     * Returns the true Collection of Roles.
     *
     * @return Collection
     */
    public function getRolesCollection()
    {
        return $this->roles;
    }

    /**
     * Pass a string, get the desired Role object or null
     *
     * @param  string $roleName Role name
     *
     * @return Role|null
     */
    public function getRole($roleName)
    {
        /** @var Role $item */
        foreach ($this->getRoles() as $item) {
            if ($roleName == $item->getRole()) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Never use this to check if this user has access to anything!
     * Use the SecurityContext, or an implementation of AccessDecisionManager
     * instead, e.g.
     *
     *         $securityContext->isGranted('ROLE_USER');
     *
     * @param  Role|string $role
     *
     * @return boolean
     * @throws \InvalidArgumentException
     */
    public function hasRole($role)
    {
        if ($role instanceof Role) {
            $roleName = $role->getRole();
        } elseif (is_string($role)) {
            $roleName = $role;
        } else {
            throw new \InvalidArgumentException(
                '$role must be an instance of Oro\Bundle\UserBundle\Entity\Role or a string'
            );
        }

        return (bool)$this->getRole($roleName);
    }

    /**
     * Adds a Role to the Collection.
     *
     * @param  Role $role
     *
     * @return User
     */
    public function addRole(Role $role)
    {
        if (!$this->hasRole($role)) {
            $this->roles->add($role);
        }

        return $this;
    }

    /**
     * Remove the Role object from collection
     *
     * @param  Role|string $role
     *
     * @throws \InvalidArgumentException
     */
    public function removeRole($role)
    {
        if ($role instanceof Role) {
            $roleObject = $role;
        } elseif (is_string($role)) {
            $roleObject = $this->getRole($role);
        } else {
            throw new \InvalidArgumentException(
                '$role must be an instance of Oro\Bundle\UserBundle\Entity\Role or a string'
            );
        }
        if ($roleObject) {
            $this->roles->removeElement($roleObject);
        }
    }

    /**
     * Pass an array or Collection of Role objects and re-set roles collection with new Roles.
     * Type hinted array due to interface.
     *
     * @param  array|Collection $roles Array of Role objects
     *
     * @return User
     * @throws \InvalidArgumentException
     */
    public function setRoles($roles)
    {
        if (!$roles instanceof Collection && !is_array($roles)) {
            throw new \InvalidArgumentException(
                '$roles must be an instance of Doctrine\Common\Collections\Collection or an array'
            );
        }

        $this->roles->clear();

        foreach ($roles as $role) {
            $this->addRole($role);
        }

        return $this;
    }

    /**
     * Directly set the Collection of Roles.
     *
     * @param  Collection $collection
     *
     * @return User
     * @throws \InvalidArgumentException
     */
    public function setRolesCollection($collection)
    {
        if (!$collection instanceof Collection) {
            throw new \InvalidArgumentException(
                '$collection must be an instance of Doctrine\Common\Collections\Collection'
            );
        }
        $this->roles = $collection;

        return $this;
    }

    /**
     * Gets the groups granted to the user
     *
     * @return Collection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @return array
     */
    public function getGroupNames()
    {
        $names = [];

        /** @var Group $group */
        foreach ($this->getGroups() as $group) {
            $names[] = $group->getName();
        }

        return $names;
    }

    /**
     * @param  string $name
     *
     * @return bool
     */
    public function hasGroup($name)
    {
        return in_array($name, $this->getGroupNames());
    }

    /**
     * @param  Group $group
     *
     * @return User
     */
    public function addGroup(Group $group)
    {
        if (!$this->getGroups()->contains($group)) {
            $this->getGroups()->add($group);
        }

        return $this;
    }

    /**
     * @param  Group $group
     *
     * @return User
     */
    public function removeGroup(Group $group)
    {
        if ($this->getGroups()->contains($group)) {
            $this->getGroups()->removeElement($group);
        }

        return $this;
    }

    /**
     * Generate unique confirmation token
     *
     * @return string Token value
     */
    public function generateToken()
    {
        return base_convert(bin2hex(hash('sha256', uniqid(mt_rand(), true), true)), 16, 36);
    }

    public function __toString()
    {
        return (string)$this->getUsername();
    }

    /**
     * Pre persist event listener
     *
     * @ORM\PrePersist
     */
    public function beforeSave()
    {
        $this->createdAt  = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->updatedAt  = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->loginCount = 0;
    }

    /**
     * Invoked before the entity is updated.
     *
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * {@inheritDoc}
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * Get User Statuses
     *
     * @return Status[]
     */
    public function getStatuses()
    {
        return $this->statuses;
    }

    /**
     * Add Status to User
     *
     * @param  Status $status
     *
     * @return User
     */
    public function addStatus(Status $status)
    {
        $this->statuses[] = $status;

        return $this;
    }

    /**
     * Get Current Status
     *
     * @return Status
     */
    public function getCurrentStatus()
    {
        return $this->currentStatus;
    }

    /**
     * Set User Current Status
     *
     * @param  Status $status
     *
     * @return User
     */
    public function setCurrentStatus(Status $status = null)
    {
        $this->currentStatus = $status;

        return $this;
    }

    /**
     * Get User Emails
     *
     * @return Email[]
     */
    public function getEmails()
    {
        return $this->emails;
    }

    /**
     * Add Email to User
     *
     * @param  Email $email
     *
     * @return User
     */
    public function addEmail(Email $email)
    {
        $this->emails[] = $email;

        $email->setUser($this);

        return $this;
    }

    /**
     * Delete Email from User
     *
     * @param  Email $email
     *
     * @return User
     */
    public function removeEmail(Email $email)
    {
        $this->emails->removeElement($email);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTaggableId()
    {
        return $this->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function getTags()
    {
        $this->tags = $this->tags ?: new ArrayCollection();

        return $this->tags;
    }

    /**
     * {@inheritdoc}
     */
    public function setTags($tags)
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getBusinessUnits()
    {
        $this->businessUnits = $this->businessUnits ?: new ArrayCollection();

        return $this->businessUnits;
    }

    /**
     * @param ArrayCollection $businessUnits
     *
     * @return User
     */
    public function setBusinessUnits($businessUnits)
    {
        $this->businessUnits = $businessUnits;

        return $this;
    }

    /**
     * @param  BusinessUnit $businessUnit
     *
     * @return User
     */
    public function addBusinessUnit(BusinessUnit $businessUnit)
    {
        if (!$this->getBusinessUnits()->contains($businessUnit)) {
            $this->getBusinessUnits()->add($businessUnit);
        }

        return $this;
    }

    /**
     * @param  BusinessUnit $businessUnit
     *
     * @return User
     */
    public function removeBusinessUnit(BusinessUnit $businessUnit)
    {
        if ($this->getBusinessUnits()->contains($businessUnit)) {
            $this->getBusinessUnits()->removeElement($businessUnit);
        }

        return $this;
    }

    /**
     * Get User Organizations
     *
     * @param  bool $onlyActive Returns enabled organizations only
     * @return ArrayCollection Organization[]
     */
    public function getOrganizations($onlyActive = false)
    {
        if ($onlyActive) {
            return $this->organizations->filter(
                function (Organization $organization) {
                    return $organization->isEnabled() === true;
                }
            );
        }
        return $this->organizations;
    }

    /**
     * @param ArrayCollection $organizations
     * @return User
     */
    public function setOrganizations($organizations)
    {
        $this->organizations = $organizations;

        return $this;
    }


    /**
     * Add Organization to User
     *
     * @param  Organization $organization
     * @return User
     */
    public function addOrganization(Organization $organization)
    {
        $this->organizations[] = $organization;

        return $this;
    }

    /**
     * Delete Organization from User
     *
     * @param  Organization $organization
     * @return User
     */
    public function removeOrganization(Organization $organization)
    {
        if ($this->hasOrganization($organization)) {
            $this->getOrganizations()->removeElement($organization);
        }

        return $this;
    }

    /**
     *
     *
     * @param Organization $organization
     * @return bool
     */
    public function hasOrganization(Organization $organization)
    {
        return $this->getOrganizations()->contains($organization);
    }

    /**
     * @return BusinessUnit
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param BusinessUnit $owningBusinessUnit
     *
     * @return User
     */
    public function setOwner($owningBusinessUnit)
    {
        $this->owner = $owningBusinessUnit;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getNotificationEmails()
    {
        return new ArrayCollection([$this->getEmail()]);
    }

    /**
     * Get IMAP configuration
     *
     * @return ImapEmailOrigin
     */
    public function getImapConfiguration()
    {
        $items = $this->emailOrigins->filter(
            function ($item) {
                return $item instanceof ImapEmailOrigin;
            }
        );

        return $items->isEmpty()
            ? null
            : $items->first();
    }

    /**
     * Set IMAP configuration
     *
     * @param ImapEmailOrigin $imapConfiguration
     *
     * @return $this
     */
    public function setImapConfiguration(ImapEmailOrigin $imapConfiguration = null)
    {
        $currentImapConfiguration = $this->getImapConfiguration();
        if (null === $imapConfiguration || $currentImapConfiguration !== $imapConfiguration) {
            // deactivate current IMAP configuration and remove a reference to it
            if ($currentImapConfiguration) {
                $currentImapConfiguration->setIsActive(false);
                $this->removeEmailOrigin($currentImapConfiguration);
            }
        }
        if (null !== $imapConfiguration) {
            $this->addEmailOrigin($imapConfiguration);
        }

        return $this;
    }

    /**
     * Get email origins assigned to user
     *
     * @return EmailOrigin[]|ArrayCollection
     */
    public function getEmailOrigins()
    {
        return $this->emailOrigins;
    }

    /**
     * Add email origin
     *
     * @param EmailOrigin $emailOrigin
     *
     * @return User
     */
    public function addEmailOrigin(EmailOrigin $emailOrigin)
    {
        $this->emailOrigins->add($emailOrigin);

        return $this;
    }

    /**
     * Delete email origin
     *
     * @param EmailOrigin $emailOrigin
     *
     * @return User
     */
    public function removeEmailOrigin(EmailOrigin $emailOrigin)
    {
        $this->emailOrigins->removeElement($emailOrigin);

        return $this;
    }

    /**
     * Set organization
     *
     * @param Organization $organization
     * @return User
     */
    public function setOrganization(Organization $organization = null)
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * Get organization
     *
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }
}
